<?php

namespace App\Http\Controllers;

use App\Models\GroupChatMessage;
use App\Models\GroupChatRead;
use App\Models\StudyGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StudentGroupController extends StudyHubController
{
    public function index(): View
    {
        return $this->renderStudent('studyhub.student.groups', [
            'groups' => $this->getStudentGroups(),
            'joinedGroupIds' => $this->getJoinedGroupIds(),
            'unreadChatGroupIds' => $this->getUnreadChatGroupIds(),
            'groupCategories' => $this->studentGroupCategories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('createGroup', [
            'name' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:160'],
            'category' => ['nullable', 'string', 'max:40'],
            'meeting_style' => ['required', 'in:in-person,online,hybrid'],
            'visibility' => ['required', 'in:public,private'],
            'join_code' => ['nullable', 'string', 'max:24'],
        ]);

        if ($validated['visibility'] === 'private') {
            $request->validateWithBag('createGroup', [
                'join_code' => ['required', 'string', 'min:4', 'max:24'],
            ]);
        }

        $group = StudyGroup::create([
            'owner_id' => $request->user()->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'color' => $this->groupColorForMeetingStyle($validated['meeting_style']),
            'category' => trim($validated['category'] ?? '') !== '' ? trim($validated['category']) : 'General',
            'meeting_style' => $validated['meeting_style'],
            'visibility' => $validated['visibility'],
            'join_code' => $validated['visibility'] === 'private' ? strtoupper(trim($validated['join_code'])) : null,
        ]);
        $group->members()->syncWithoutDetaching([$request->user()->id]);

        return redirect()
            ->route('studyhub.student.groups')
            ->with('status', 'New study group created successfully.');
    }

    public function join(Request $request, StudyGroup $group): RedirectResponse
    {
        if ($group->members()->where('users.id', $request->user()->id)->exists()) {
            return back()->with('status', 'You already joined this group.');
        }

        if ($group->visibility === 'private') {
            $validated = $request->validateWithBag('joinGroup', [
                'join_code' => ['required', 'string', 'max:24'],
            ]);

            if (strtoupper(trim($validated['join_code'])) !== ($group->join_code ?? '')) {
                return back()
                    ->withErrors(['join_code' => 'Wrong join code for this private group.'], 'joinGroup')
                    ->withInput($request->only('join_code', 'join_group_id'))
                    ->with('status', 'Private group code did not match.');
            }
        }

        $group->members()->attach($request->user()->id);
        $this->logActivity(
            'group_joined',
            'Group joined',
            ($request->user()->display_name ?: $request->user()->name).' joined '.$group->name.'.',
            $group,
            $group,
        );

        return back()->with('status', 'You joined the study group.');
    }

    public function leave(Request $request, StudyGroup $group): RedirectResponse
    {
        if (! $group->members()->where('users.id', $request->user()->id)->exists()) {
            return back()->with('status', 'You are not a member of this group.');
        }

        if ((int) $group->owner_id === (int) $request->user()->id && $group->members()->count() === 1) {
            return back()->with('status', 'As the only member, you cannot leave your own group yet.');
        }

        $group->members()->detach($request->user()->id);

        return back()->with('status', 'You left the study group.');
    }

    public function show(StudyGroup $group): View|RedirectResponse
    {
        $groupModel = $group->loadCount(['members', 'resources']);

        if (Gate::denies('view', $groupModel)) {
            return redirect()
                ->route('studyhub.student.groups')
                ->with('status', 'That study group could not be found.');
        }

        $group = $this->formatGroup($groupModel);
        $canViewContent = Gate::allows('viewContent', $groupModel);

        $resources = $canViewContent
            ? collect($this->getStudentResources())->where('group', $group['name'])->values()->all()
            : [];
        $discussions = $canViewContent
            ? collect($this->getStudentDiscussions())
                ->where('group', $group['name'])
                ->take(3)
                ->map(fn (array $discussion) => [
                    'title' => $discussion['title'],
                    'author' => $discussion['author'],
                    'replies' => $discussion['replies'],
                    'last_active' => $discussion['last_active'],
                ])
                ->values()
                ->all()
            : [];
        $sessions = $canViewContent
            ? collect($this->getStudentSessions())->where('group', $group['name'])->where('phase', 'upcoming')->values()->all()
            : [];

        return $this->renderStudent('studyhub.student.group-detail', [
            'group' => $group,
            'resources' => $resources,
            'discussions' => $discussions,
            'sessions' => $sessions,
            'isJoined' => in_array((int) $group['id'], $this->getJoinedGroupIds(), true),
            'joinCodeError' => session('errors')?->first('join_code'),
            'resourceCategories' => $this->studentResourceCategoriesWithoutAll(),
        ]);
    }

    public function storeMessage(Request $request, StudyGroup $group): RedirectResponse
    {
        if (Gate::denies('createContent', $group)) {
            return redirect($request->headers->get('referer') ?: route('studyhub.student.group.show', $group))
                ->with('status', 'Join this group before using its chat.');
        }

        $validated = $request->validateWithBag('groupChat', [
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $message = GroupChatMessage::create([
            'study_group_id' => $group->id,
            'user_id' => $request->user()->id,
            'body' => trim($validated['body']),
        ]);

        $this->markGroupChatRead($group);
        $this->logActivity(
            'group_chat_message_posted',
            'Group chat message posted',
            ($request->user()->display_name ?: $request->user()->name).' posted in '.$group->name.' chat.',
            $group,
            $message,
        );

        return redirect($request->headers->get('referer') ?: route('studyhub.student.group.show', $group))
            ->with('open_chat', true)
            ->with('open_chat_thread_id', $group->id)
            ->with('status', 'Message sent.');
    }

    private function getUnreadChatGroupIds(): array
    {
        $user = $this->studentUser();
        $joinedGroupIds = $this->getJoinedGroupIds();

        if (empty($joinedGroupIds)) {
            return [];
        }

        return StudyGroup::query()
            ->whereIn('id', $joinedGroupIds)
            ->whereHas('chatMessages', function ($query) use ($user) {
                $query
                    ->where('user_id', '<>', $user->id)
                    ->where(function ($query) use ($user) {
                        $query
                            ->whereDoesntHave('group.chatReads', fn ($query) => $query->where('user_id', $user->id))
                            ->orWhereHas('group.chatReads', function ($query) use ($user) {
                                $query->where('user_id', $user->id)
                                    ->whereColumn('group_chat_reads.last_read_at', '<', 'group_chat_messages.created_at');
                            });
                    });
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

}
