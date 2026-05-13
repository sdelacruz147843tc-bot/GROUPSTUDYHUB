<?php

namespace App\Http\Controllers;

use App\Models\ResourceFolder;
use App\Models\SavedResource;
use App\Models\StudyGroup;
use App\Models\StudyResource;
use App\Models\StudyResourceReview;
use App\Services\StudyHub\StudyHubFormatter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentResourceController extends StudyHubController
{
    public function index(Request $request): View
    {
        $filters = $this->resourceSearchFilters($request);

        return $this->renderStudent('studyhub.student.resources', [
            'categories' => $this->studentResourceCategories(),
            'resources' => $this->getPaginatedStudentResources(filters: $filters),
            'uploadGroups' => $this->getJoinedGroups(),
            'searchFilters' => $filters,
            'resourceGroups' => $this->getStudentResourceFilterGroups(),
            'activeFilterCount' => $this->activeResourceFilterCount($filters),
            'resourceOverview' => $this->resourceLibraryOverview(),
            'resourceCategoryUsage' => $this->resourceCategoryUsage(),
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
        $fileType = strtolower($file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION) ?: 'file');

        $resource = StudyResource::create([
            'group_id' => (int) $validated['group_id'],
            'uploaded_by' => $request->user()->id,
            'name' => $file->getClientOriginalName(),
            'category' => $validated['category'],
            'path' => $storedPath,
            'mime_type' => $file->getMimeType(),
            'file_type' => mb_substr($fileType, 0, 24),
            'size_bytes' => $file->getSize(),
            'uploaded_at' => now(),
        ]);

        SavedResource::updateOrCreate([
            'user_id' => $request->user()->id,
            'study_resource_id' => $resource->id,
        ], [
            'resource_folder_id' => null,
            'saved_at' => now(),
        ]);

        $this->logActivity(
            'resource_uploaded',
            'Resource uploaded',
            ($request->user()->display_name ?: $request->user()->name).' uploaded '.$resource->name.' to '.$group->name.'.',
            $group,
            $resource,
        );

        $redirectTo = $request->string('redirect_to')->toString();

        if ($redirectTo !== '' && (
            str_starts_with($redirectTo, url('/studyhub/student/groups/'))
            || str_starts_with($redirectTo, url('/studyhub/student/library'))
        )) {
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

        $this->recordResourceView($resource, request()->user());

        if ($resource->path && Storage::disk('local')->exists($resource->path)) {
            $resource->increment('download_count');

            return Storage::disk('local')->download($resource->path, $resource->name);
        }

        if ($resource->path && Storage::disk('public')->exists($resource->path)) {
            $resource->increment('download_count');

            return Storage::disk('public')->download($resource->path, $resource->name);
        }

        return redirect()
            ->route('studyhub.student.resources')
            ->with('status', 'That resource file could not be found.');
    }

    public function view(StudyResource $resource): RedirectResponse|StreamedResponse
    {
        if (Gate::denies('view', $resource)) {
            return redirect()
                ->route('studyhub.student.resources')
                ->with('status', 'You need access to that group before viewing its resource.');
        }

        $this->recordResourceView($resource, request()->user());

        if ($resource->path && Storage::disk('local')->exists($resource->path)) {
            return Storage::disk('local')->response($resource->path, $resource->name);
        }

        if ($resource->path && Storage::disk('public')->exists($resource->path)) {
            return Storage::disk('public')->response($resource->path, $resource->name);
        }

        return redirect()
            ->route('studyhub.student.resources')
            ->with('status', 'That resource file could not be found.');
    }

    public function review(Request $request, StudyResource $resource): RedirectResponse
    {
        $resource->loadMissing('group');

        if (Gate::denies('view', $resource)) {
            return redirect()
                ->route('studyhub.student.resources')
                ->with('status', 'You need access to that group before rating its resource.');
        }

        $validated = $request->validate([
            'accuracy_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'clarity_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'usefulness_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review_text' => ['nullable', 'string', 'max:600'],
        ]);

        StudyResourceReview::updateOrCreate([
            'study_resource_id' => $resource->id,
            'user_id' => $request->user()->id,
        ], [
            'accuracy_rating' => (int) $validated['accuracy_rating'],
            'clarity_rating' => (int) $validated['clarity_rating'],
            'usefulness_rating' => (int) $validated['usefulness_rating'],
            'review_text' => $this->nullableResourceInput($validated['review_text'] ?? null),
        ]);

        $this->refreshResourceRatingSummary($resource);

        return $this->resourceReviewRedirect($request)
            ->with('status', 'Thanks for rating this resource.');
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

    private function refreshResourceRatingSummary(StudyResource $resource): void
    {
        $summary = $resource->reviews()
            ->selectRaw('count(*) as review_count')
            ->selectRaw('avg((accuracy_rating + clarity_rating + usefulness_rating) / 3.0) as rating_average')
            ->first();

        $resource->forceFill([
            'rating_average' => round((float) ($summary?->rating_average ?? 0), 2),
            'rating_count' => (int) ($summary?->review_count ?? 0),
        ])->save();
    }

    private function resourceReviewRedirect(Request $request): RedirectResponse
    {
        $redirectTo = $request->string('redirect_to')->toString();

        if ($redirectTo !== '' && str_starts_with($redirectTo, url('/studyhub/'))) {
            return redirect($redirectTo);
        }

        return redirect()->route('studyhub.student.resources');
    }

    private function resourceSearchFilters(Request $request): array
    {
        $sort = $request->string('sort')->toString();
        $availability = $request->string('availability')->toString();

        return [
            'q' => $this->cleanResourceFilterValue($request->input('q'), 100),
            'category' => $this->cleanResourceFilterValue($request->input('category')) === 'all' ? '' : $this->cleanResourceFilterValue($request->input('category')),
            'group_id' => ctype_digit((string) $request->input('group_id')) ? (int) $request->input('group_id') : null,
            'availability' => in_array($availability, ['downloadable', 'unavailable'], true) ? $availability : '',
            'sort' => in_array($sort, ['newest', 'most_downloaded', 'highest_rated'], true) ? $sort : 'newest',
        ];
    }

    private function cleanResourceFilterValue(mixed $value, int $limit = 80): string
    {
        return mb_substr(trim((string) $value), 0, $limit);
    }

    private function nullableResourceInput(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function activeResourceFilterCount(array $filters): int
    {
        return collect($filters)
            ->reject(fn ($value, string $key) => $key === 'sort' || $value === '' || $value === null || $value === false)
            ->count();
    }

    private function resourceLibraryOverview(): array
    {
        $visibleResources = $this->visibleResourcesQuery();

        return [
            'total_files' => (clone $visibleResources)->count(),
            'folders' => ResourceFolder::query()
                ->where('user_id', $this->studentUser()->id)
                ->count(),
            'total_size' => app(StudyHubFormatter::class)->fileSize((int) (clone $visibleResources)->sum('size_bytes')),
            'favorites' => (clone $visibleResources)
                ->whereHas('savedResources', fn ($query) => $query->where('user_id', $this->studentUser()->id))
                ->count(),
        ];
    }

    private function resourceCategoryUsage(): array
    {
        return $this->visibleResourcesQuery()
            ->selectRaw('category, count(*) as aggregate')
            ->groupBy('category')
            ->orderByDesc('aggregate')
            ->limit(5)
            ->pluck('aggregate', 'category')
            ->map(fn ($count, string $category) => [
                'name' => $category,
                'count' => (int) $count,
            ])
            ->values()
            ->all();
    }
}
