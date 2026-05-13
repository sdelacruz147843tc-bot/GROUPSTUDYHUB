<?php

namespace App\Http\Controllers;

use App\Models\ResourceFolder;
use App\Models\ResourceView;
use App\Models\SavedResource;
use App\Models\StudyResource;
use App\Services\StudyHub\StudyHubFormatter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StudentLibraryController extends StudyHubController
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $viewerId = $user->id;
        $folderFilter = $this->libraryFolderFilter($request);
        $filters = $this->libraryFilters($request);
        $search = mb_substr(trim($request->string('q')->toString()), 0, 100);

        $savedQuery = $this->visibleSavedResourcesQuery($user->id)
            ->with([
                'folder',
                'resource' => fn ($query) => $query->with($this->studentResourceDisplayRelations($viewerId)),
            ]);

        if ($folderFilter['type'] === 'folder') {
            $savedQuery->where('resource_folder_id', $folderFilter['id']);
        }

        if ($folderFilter['type'] === 'unfiled') {
            $savedQuery->whereNull('resource_folder_id');
        }

        if ($search !== '') {
            $like = '%'.$search.'%';

            $savedQuery->whereHas('resource', function ($query) use ($like) {
                $query
                    ->where('name', 'like', $like)
                    ->orWhere('category', 'like', $like)
                    ->orWhereHas('group', fn ($query) => $query->where('name', 'like', $like))
                    ->orWhereHas('uploader', function ($query) use ($like) {
                        $query
                            ->where('name', 'like', $like)
                            ->orWhere('display_name', 'like', $like)
                            ->orWhere('email', 'like', $like);
                    });
            });
        }

        if ($filters['file_type'] !== '') {
            $savedQuery->whereHas('resource', fn ($query) => $query->where('file_type', $filters['file_type']));
        }

        if ($filters['availability'] === 'downloadable') {
            $savedQuery->whereHas('resource', function ($query) {
                $query
                    ->whereNotNull('path')
                    ->where('path', '<>', '');
            });
        }

        if ($filters['item'] === 'recent') {
            $savedQuery->whereHas('resource.views', fn ($query) => $query->where('user_id', $user->id));
        }

        match ($filters['sort']) {
            'oldest' => $savedQuery->orderBy('saved_at'),
            'name' => $savedQuery->join('study_resources as sorted_resources', 'saved_resources.study_resource_id', '=', 'sorted_resources.id')
                ->orderBy('sorted_resources.name')
                ->select('saved_resources.*'),
            default => $savedQuery->orderByDesc('saved_at'),
        };

        $savedResources = $savedQuery
            ->paginate(9)
            ->withQueryString()
            ->through(fn (SavedResource $savedResource) => $this->formatLibraryResource($savedResource));

        $recentResources = ResourceView::query()
            ->where('user_id', $user->id)
            ->whereHas('resource.group', fn ($query) => $this->applyVisibleGroupContentConstraint($query, $user))
            ->with([
                'resource' => fn ($query) => $query->with($this->studentResourceDisplayRelations($viewerId)),
            ])
            ->orderByDesc('viewed_at')
            ->limit(6)
            ->get()
            ->map(fn (ResourceView $view) => array_merge($this->formatResource($view->resource), [
                'viewed_at' => $this->humanizeTime($view->viewed_at),
            ]))
            ->all();

        $folderCounts = $this->visibleSavedResourcesQuery($user->id)
            ->selectRaw('resource_folder_id, count(*) as aggregate')
            ->groupBy('resource_folder_id')
            ->pluck('aggregate', 'resource_folder_id');

        $totalSizeBytes = (int) $this->visibleSavedResourcesQuery($user->id)
            ->join('study_resources', 'saved_resources.study_resource_id', '=', 'study_resources.id')
            ->sum('study_resources.size_bytes');

        $folders = ResourceFolder::query()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get()
            ->map(fn (ResourceFolder $folder) => [
                'id' => $folder->id,
                'name' => $folder->name,
                'color' => $folder->color,
                'saved_count' => (int) ($folderCounts[$folder->id] ?? 0),
            ])
            ->all();

        $savedCount = (int) $folderCounts->sum();
        $unfiledCount = (int) ($folderCounts[''] ?? $folderCounts[null] ?? 0);

        return $this->renderStudent('studyhub.student.library', [
            'savedResources' => $savedResources,
            'recentResources' => $recentResources,
            'folders' => $folders,
            'folderOptions' => $this->resourceFolderOptions($user->id),
            'folderColors' => $this->folderColorOptions(),
            'uploadGroups' => $this->getJoinedGroups(),
            'categories' => $this->studentResourceCategories(),
            'selectedFolder' => $folderFilter,
            'libraryFilters' => $filters,
            'libraryFileTypes' => $this->libraryFileTypes($user->id),
            'librarySearch' => $search,
            'libraryStats' => [
                'saved' => $savedCount,
                'folders' => count($folders),
                'recent' => count($recentResources),
                'unfiled' => $unfiledCount,
                'total_size' => app(StudyHubFormatter::class)->fileSize($totalSizeBytes),
            ],
        ]);
    }

    public function save(Request $request, StudyResource $resource): RedirectResponse
    {
        $resource->loadMissing('group');

        if (Gate::denies('view', $resource)) {
            return back()->with('status', 'You need access to that group before saving its resource.');
        }

        $folderId = $this->validatedFolderId($request);

        $savedResource = SavedResource::updateOrCreate([
            'user_id' => $request->user()->id,
            'study_resource_id' => $resource->id,
        ], [
            'resource_folder_id' => $folderId,
            'saved_at' => now(),
        ]);

        return back()->with('status', $savedResource->wasRecentlyCreated
            ? 'Resource saved to My Library.'
            : 'Resource moved in My Library.');
    }

    public function unsave(Request $request, StudyResource $resource): RedirectResponse
    {
        SavedResource::query()
            ->where('user_id', $request->user()->id)
            ->where('study_resource_id', $resource->id)
            ->delete();

        return back()->with('status', 'Resource removed from My Library.');
    }

    public function storeFolder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('resource_folders', 'name')
                    ->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
            'color' => ['required', Rule::in($this->folderColorOptions())],
        ]);

        ResourceFolder::create([
            'user_id' => $request->user()->id,
            'name' => trim($validated['name']),
            'color' => $validated['color'],
        ]);

        return back()->with('status', 'Folder created.');
    }

    public function updateSaved(Request $request, SavedResource $savedResource): RedirectResponse
    {
        if ((int) $savedResource->user_id !== (int) $request->user()->id) {
            return back()->with('status', 'You can only organize resources in your own library.');
        }

        $savedResource->update([
            'resource_folder_id' => $this->validatedFolderId($request),
        ]);

        return back()->with('status', 'Saved resource moved.');
    }

    public function destroyFolder(Request $request, ResourceFolder $folder): RedirectResponse
    {
        if ((int) $folder->user_id !== (int) $request->user()->id) {
            return back()->with('status', 'You can only delete your own folders.');
        }

        $folder->savedResources()->update(['resource_folder_id' => null]);
        $folder->delete();

        return redirect()
            ->route('studyhub.student.library')
            ->with('status', 'Folder removed. Saved resources were kept.');
    }

    private function visibleSavedResourcesQuery(int $userId)
    {
        $user = $this->studentUser();

        return SavedResource::query()
            ->where('user_id', $userId)
            ->whereHas('resource.group', fn ($query) => $this->applyVisibleGroupContentConstraint($query, $user));
    }

    private function formatLibraryResource(SavedResource $savedResource): array
    {
        return array_merge($this->formatResource($savedResource->resource), [
            'library_saved_id' => $savedResource->id,
            'library_saved_at' => $this->humanizeTime($savedResource->saved_at),
            'library_folder_id' => $savedResource->resource_folder_id,
            'library_folder' => $savedResource->folder?->name ?: 'Unfiled',
        ]);
    }

    private function resourceFolderOptions(int $userId): array
    {
        return ResourceFolder::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get(['id', 'name', 'color'])
            ->map(fn (ResourceFolder $folder) => [
                'id' => $folder->id,
                'name' => $folder->name,
                'color' => $folder->color,
            ])
            ->all();
    }

    private function validatedFolderId(Request $request): ?int
    {
        $validated = $request->validate([
            'resource_folder_id' => [
                'nullable',
                'integer',
                Rule::exists('resource_folders', 'id')
                    ->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
        ]);

        return empty($validated['resource_folder_id']) ? null : (int) $validated['resource_folder_id'];
    }

    private function libraryFolderFilter(Request $request): array
    {
        $folder = $request->string('folder')->toString();

        if ($folder === 'unfiled') {
            return ['type' => 'unfiled', 'id' => null, 'name' => 'Unfiled'];
        }

        if (ctype_digit($folder)) {
            $resourceFolder = ResourceFolder::query()
                ->where('user_id', $request->user()->id)
                ->find((int) $folder);

            if ($resourceFolder) {
                return ['type' => 'folder', 'id' => $resourceFolder->id, 'name' => $resourceFolder->name];
            }
        }

        return ['type' => 'all', 'id' => null, 'name' => 'All saved'];
    }

    private function folderColorOptions(): array
    {
        return ['#22c55e', '#0ea5e9', '#8b5cf6', '#f59e0b', '#ef4444'];
    }

    private function libraryFilters(Request $request): array
    {
        $fileType = mb_substr(strtolower(trim($request->string('file_type')->toString())), 0, 24);
        $sort = $request->string('sort')->toString();
        $availability = $request->string('availability')->toString();
        $item = $request->string('item')->toString();

        return [
            'file_type' => preg_match('/^[a-z0-9]+$/', $fileType) ? $fileType : '',
            'sort' => in_array($sort, ['newest', 'oldest', 'name'], true) ? $sort : 'newest',
            'availability' => in_array($availability, ['all', 'downloadable'], true) ? $availability : 'all',
            'item' => in_array($item, ['all', 'recent'], true) ? $item : 'all',
        ];
    }

    private function libraryFileTypes(int $userId): array
    {
        return $this->visibleSavedResourcesQuery($userId)
            ->join('study_resources', 'saved_resources.study_resource_id', '=', 'study_resources.id')
            ->whereNotNull('study_resources.file_type')
            ->where('study_resources.file_type', '<>', '')
            ->distinct()
            ->orderBy('study_resources.file_type')
            ->pluck('study_resources.file_type')
            ->map(fn (string $type) => strtolower($type))
            ->unique()
            ->values()
            ->all();
    }
}
