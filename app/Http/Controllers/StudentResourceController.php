<?php

namespace App\Http\Controllers;

use App\Models\StudyGroup;
use App\Models\StudyResource;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentResourceController extends StudyHubController
{
    public function index(): View
    {
        return $this->renderStudent('studyhub.student.resources', [
            'categories' => $this->studentResourceCategories(),
            'resources' => $this->getPaginatedStudentResources(),
            'uploadGroups' => $this->getJoinedGroups(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $joinedGroupIds = collect($this->getJoinedGroups())
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $validated = $request->validate([
            'group_id' => ['required', 'in:'.implode(',', $joinedGroupIds)],
            'category' => ['required', 'in:'.implode(',', $this->studentResourceCategoriesWithoutAll())],
            'resource_file' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,csv,md,rtf,zip,jpg,jpeg,png',
            ],
        ]);

        $group = StudyGroup::find((int) $validated['group_id']);

        if (! $group || Gate::denies('createContent', $group)) {
            return back()->with('status', 'You can only upload resources to groups you joined.');
        }

        $file = $request->file('resource_file');
        $storedPath = $file->store('studyhub-resources');

        $resource = StudyResource::create([
            'group_id' => (int) $validated['group_id'],
            'uploaded_by' => $request->user()->id,
            'name' => $file->getClientOriginalName(),
            'category' => $validated['category'],
            'path' => $storedPath,
            'size_bytes' => $file->getSize(),
            'uploaded_at' => now(),
        ]);
        $this->logActivity(
            'resource_uploaded',
            'Resource uploaded',
            ($request->user()->display_name ?: $request->user()->name).' uploaded '.$resource->name.' to '.$group->name.'.',
            $group,
            $resource,
        );

        $redirectTo = $request->string('redirect_to')->toString();

        if ($redirectTo !== '' && str_starts_with($redirectTo, url('/studyhub/student/groups/'))) {
            return redirect($redirectTo)->with('status', 'Resource uploaded successfully.');
        }

        return redirect()
            ->route('studyhub.student.resources')
            ->with('status', 'Resource uploaded successfully.');
    }

    public function download(StudyResource $resource): RedirectResponse|StreamedResponse
    {
        if (Gate::denies('view', $resource)) {
            return redirect()
                ->route('studyhub.student.resources')
                ->with('status', 'You need access to that group before downloading its resource.');
        }

        if ($resource->path && Storage::disk('local')->exists($resource->path)) {
            return Storage::disk('local')->download($resource->path, $resource->name);
        }

        if ($resource->path && Storage::disk('public')->exists($resource->path)) {
            return Storage::disk('public')->download($resource->path, $resource->name);
        }

        return redirect()
            ->route('studyhub.student.resources')
            ->with('status', 'That resource file could not be found.');
    }

    public function destroy(Request $request, StudyResource $resource): RedirectResponse
    {
        $resource->loadMissing(['group', 'uploader']);

        if (Gate::denies('delete', $resource)) {
            return $this->resourceDeletionRedirect($request)
                ->with('status', 'You can only delete resources you uploaded or resources in groups you own.');
        }

        $group = $resource->group;
        $resourceName = $resource->name;
        $storedPath = $resource->path;
        $actorName = $request->user()->display_name ?: $request->user()->name;
        $groupName = $group?->name ?: 'a StudyHub group';

        $this->logActivity(
            'resource_deleted',
            'Resource deleted',
            $actorName.' deleted '.$resourceName.' from '.$groupName.'.',
            $group,
            $resource,
        );

        $resource->delete();
        $this->deleteStoredResourceFile($storedPath);

        return $this->resourceDeletionRedirect($request)
            ->with('status', 'Resource deleted successfully.');
    }

    private function resourceDeletionRedirect(Request $request): RedirectResponse
    {
        $redirectTo = $request->string('redirect_to')->toString();

        if ($redirectTo !== '' && str_starts_with($redirectTo, url('/studyhub/'))) {
            return redirect($redirectTo);
        }

        if ($request->user()?->isAdmin()) {
            return redirect()->route('studyhub.admin.groups');
        }

        return redirect()->route('studyhub.student.resources');
    }

    private function deleteStoredResourceFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        collect(['local', 'public'])
            ->each(fn (string $disk) => Storage::disk($disk)->delete($path));
    }
}
