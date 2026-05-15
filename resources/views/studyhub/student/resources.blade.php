@extends('studyhub.student.layout')

@section('title', 'Resource Library')

@php
    $resourceThemeFor = function (array $resource, string $extension): array {
        $key = strtolower($extension);
        $category = strtolower($resource['category'] ?? '');
        $name = strtolower($resource['name'] ?? '');

        if (str_contains($category, 'lecture') || $key === 'pdf') {
            return ['rgb' => '239, 68, 68', 'soft' => '254, 226, 226', 'text' => '#dc2626'];
        }

        if (in_array($key, ['doc', 'docx'], true)) {
            return ['rgb' => '59, 130, 246', 'soft' => '219, 234, 254', 'text' => '#2563eb'];
        }

        if (in_array($key, ['ppt', 'pptx'], true) || str_contains($name, 'presentation')) {
            return ['rgb' => '249, 115, 22', 'soft' => '255, 237, 213', 'text' => '#ea580c'];
        }

        if (in_array($key, ['xls', 'xlsx', 'csv'], true) || str_contains($name, 'data')) {
            return ['rgb' => '34, 197, 94', 'soft' => '220, 252, 231', 'text' => '#16a34a'];
        }

        if (str_contains($category, 'guide') || str_contains($name, 'formula')) {
            return ['rgb' => '168, 85, 247', 'soft' => '243, 232, 255', 'text' => '#9333ea'];
        }

        return ['rgb' => '245, 158, 11', 'soft' => '254, 243, 199', 'text' => '#d97706'];
    };

    $filters = $searchFilters ?? [
        'q' => '',
        'category' => '',
        'group_id' => null,
        'availability' => '',
        'sort' => 'newest',
    ];
    $resourceGroups = $resourceGroups ?? [];
    $resourceTotal = method_exists($resources, 'total') ? $resources->total() : count($resources);
    $resourceItems = collect(method_exists($resources, 'items') ? $resources->items() : $resources);
    $continueItems = $resourceItems->take(4);
    $hasActiveFilters = ($activeFilterCount ?? 0) > 0;
    $reviewRedirectUrl = url()->full();
    $overview = $resourceOverview ?? [
        'total_files' => $resourceTotal,
        'folders' => 0,
        'total_size' => '0 KB',
        'favorites' => 0,
    ];
@endphp

@section('page')
    <div class="resource-library-page">
        <section class="resource-library-hero">
            <div>
                <h2 class="page-title">Resource Library</h2>
                <p class="page-subtitle">Access all your study materials in one place</p>
            </div>
            <button class="resource-mobile-upload-button" type="button" data-resource-upload-open>
                <span class="icon-box">{!! $icons['upload-cloud'] ?? $icons['plus'] !!}</span>
                <span>Upload Resources</span>
            </button>
            <div class="resource-hero-art" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </section>

        <nav class="file-scope-switch" aria-label="File library views">
            <a class="active" href="{{ route('studyhub.student.resources') }}">All Resources</a>
            <a href="{{ route('studyhub.student.library') }}">My Library</a>
        </nav>

        <div class="resource-library-shell">
            <main class="resource-library-main">
                <form class="resource-filter-panel" method="GET" action="{{ route('studyhub.student.resources') }}" data-resource-filter-form>
                    <div class="resource-filter-primary">
                        <label class="resource-search-field">
                            <span class="icon-box">{!! $icons['search'] !!}</span>
                            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search file name, group, category, or uploader..." data-resource-search>
                        </label>

                        <label class="resource-sort-field">
                            <span>Sort by:</span>
                            <select name="sort" aria-label="Sort resources" data-resource-auto-submit>
                                <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                                <option value="most_downloaded" @selected($filters['sort'] === 'most_downloaded')>Most downloaded</option>
                                <option value="highest_rated" @selected($filters['sort'] === 'highest_rated')>Highest rated</option>
                            </select>
                        </label>

                        <button class="resource-view-toggle active" type="button" aria-label="Grid view" aria-pressed="true" data-resource-view-toggle="grid">
                            <span></span><span></span><span></span><span></span>
                        </button>
                        <button class="resource-view-toggle" type="button" aria-label="List view" aria-pressed="false" data-resource-view-toggle="list">
                            <span></span><span></span><span></span>
                        </button>
                    </div>

                    <div class="resource-filter-grid">
                        <label>
                            <span>Category</span>
                            <select name="category" data-resource-auto-submit>
                                <option value="">All categories</option>
                                @foreach (array_slice($categories, 1) as $category)
                                    <option value="{{ $category }}" @selected($filters['category'] === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>Group</span>
                            <select name="group_id" data-resource-auto-submit>
                                <option value="">All groups</option>
                                @foreach ($resourceGroups as $groupOption)
                                    <option value="{{ $groupOption['id'] }}" @selected((string) $filters['group_id'] === (string) $groupOption['id'])>{{ $groupOption['name'] }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>Availability</span>
                            <select name="availability" data-resource-auto-submit>
                                <option value="">All files</option>
                                <option value="downloadable" @selected($filters['availability'] === 'downloadable')>Downloadable only</option>
                                <option value="unavailable" @selected($filters['availability'] === 'unavailable')>Unavailable files</option>
                            </select>
                        </label>

                        <button class="resource-filter-submit" type="submit">
                            <span class="icon-box">{!! $icons['settings'] !!}</span>
                            <span>Filter</span>
                        </button>

                        @if ($hasActiveFilters)
                            <a class="resource-filter-clear" href="{{ route('studyhub.student.resources') }}">Clear all</a>
                        @endif
                    </div>
                </form>

                <section class="resource-section">
                    <div class="resource-section-head">
                        <h3>Continue where you left off</h3>
                        <a href="#resource-all-files">View all</a>
                    </div>

                    @if ($continueItems->isNotEmpty())
                        <div class="resource-continue-grid">
                            @foreach ($continueItems as $resource)
                                @php
                                    $extension = strtoupper($resource['file_type'] ?: pathinfo($resource['name'], PATHINFO_EXTENSION) ?: 'FILE');
                                    $resourceTheme = $resourceThemeFor($resource, $extension);
                                    $progress = min(90, 30 + ($loop->iteration * 15));
                                @endphp
                                <article class="resource-study-card" style="--resource-rgb: {{ $resourceTheme['rgb'] }}; --resource-soft-rgb: {{ $resourceTheme['soft'] }}; --resource-text: {{ $resourceTheme['text'] }};">
                                    <form class="resource-card-save-form" method="POST" action="{{ ! empty($resource['is_saved']) ? route('studyhub.student.resources.unsave', $resource['id']) : route('studyhub.student.resources.save', $resource['id']) }}">
                                        @csrf
                                        @if (! empty($resource['is_saved']))
                                            @method('DELETE')
                                        @endif
                                        <button class="{{ ! empty($resource['is_saved']) ? 'is-saved' : '' }}" type="submit" aria-label="{{ ! empty($resource['is_saved']) ? 'Remove '.$resource['name'].' from My Library' : 'Save '.$resource['name'].' to My Library' }}">
                                            <span class="icon-box">{!! $icons['library'] !!}</span>
                                            <span>{{ ! empty($resource['is_saved']) ? 'Saved' : 'Save' }}</span>
                                        </button>
                                    </form>
                                    <div class="resource-file-badge">
                                        <span class="icon-box">{!! $icons['file'] !!}</span>
                                        <strong>{{ $extension }}</strong>
                                    </div>
                                    <h4>{{ $resource['name'] }}</h4>
                                    <p>{{ $resource['group'] }}</p>
                                   
                                    <small>Last opened {{ $resource['date'] }}</small>
                                    <div class="resource-quick-actions">
                                        <a class="{{ empty($resource['path']) ? 'is-disabled' : '' }}" href="{{ ! empty($resource['path']) ? route('studyhub.student.resources.view', $resource['id']) : '#' }}" target="_blank" rel="noopener" @if (empty($resource['path'])) aria-disabled="true" @endif>Open</a>
                                        <button type="button" data-resource-review-open data-review-action="{{ route('studyhub.student.resources.reviews.store', $resource['id']) }}" data-review-resource="{{ $resource['name'] }}" data-review-accuracy="{{ $resource['viewer_review']['accuracy_rating'] ?? '' }}" data-review-clarity="{{ $resource['viewer_review']['clarity_rating'] ?? '' }}" data-review-usefulness="{{ $resource['viewer_review']['usefulness_rating'] ?? '' }}" data-review-text="{{ $resource['viewer_review']['review_text'] ?? '' }}">Rate</button>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="resources-empty-state app-empty-state is-visible">
                            <span class="app-empty-icon">{!! $icons['file'] !!}</span>
                            <strong>{{ $hasActiveFilters ? 'No resources match your filters' : 'No resources yet' }}</strong>
                            <span>{{ $hasActiveFilters ? 'Try fewer filters or a broader file search.' : 'Files uploaded to your joined groups will appear here.' }}</span>
                        </div>
                    @endif
                </section>

                <section class="resource-section" id="resource-all-files">
                    <div class="resource-section-head">
                        <div>
                            <h3>All Files</h3>
                            <span>{{ $resourceTotal }} items</span>
                        </div>
                    </div>

                    @if ($resourceItems->isNotEmpty())
                        <div class="resource-files-grid" data-resource-files-view>
                            @foreach ($resourceItems as $resource)
                                @php
                                    $extension = strtoupper($resource['file_type'] ?: pathinfo($resource['name'], PATHINFO_EXTENSION) ?: 'FILE');
                                    $resourceTheme = $resourceThemeFor($resource, $extension);
                                    $resourceSearchText = strtolower(implode(' ', [
                                        $resource['name'] ?? '',
                                        $resource['group'] ?? '',
                                        $resource['category'] ?? '',
                                        $resource['uploaded_by'] ?? '',
                                        $extension,
                                    ]));
                                @endphp
                                <article class="resource-file-card" style="--resource-rgb: {{ $resourceTheme['rgb'] }}; --resource-soft-rgb: {{ $resourceTheme['soft'] }}; --resource-text: {{ $resourceTheme['text'] }};" data-resource-file-card data-resource-search-text="{{ $resourceSearchText }}">
                                    <div class="resource-file-card-top">
                                        <span class="icon-box resource-file-icon">{!! $icons['file'] !!}</span>
                                        <form class="resource-card-save-form" method="POST" action="{{ ! empty($resource['is_saved']) ? route('studyhub.student.resources.unsave', $resource['id']) : route('studyhub.student.resources.save', $resource['id']) }}">
                                            @csrf
                                            @if (! empty($resource['is_saved']))
                                                @method('DELETE')
                                            @endif
                                            <button class="{{ ! empty($resource['is_saved']) ? 'is-saved' : '' }}" type="submit" aria-label="{{ ! empty($resource['is_saved']) ? 'Remove '.$resource['name'].' from My Library' : 'Save '.$resource['name'].' to My Library' }}">
                                                <span class="icon-box">{!! $icons['library'] !!}</span>
                                                <span>{{ ! empty($resource['is_saved']) ? 'Saved' : 'Save' }}</span>
                                            </button>
                                        </form>
                                    </div>
                                    <h4>{{ $resource['name'] }}</h4>
                                    <p>{{ $resource['size'] }} &middot; {{ $extension }}</p>
                                    <small>Uploaded {{ $resource['date'] }}</small>
                                    <div class="resource-card-actions">
                                        <a class="{{ empty($resource['path']) ? 'is-disabled' : '' }}" href="{{ ! empty($resource['path']) ? route('studyhub.student.resources.view', $resource['id']) : '#' }}" target="_blank" rel="noopener" @if (empty($resource['path'])) aria-disabled="true" @endif>Open</a>
                                        <a class="{{ empty($resource['path']) ? 'is-disabled' : '' }}" href="{{ ! empty($resource['path']) ? route('studyhub.student.resources.download', $resource['id']) : '#' }}" @if (empty($resource['path'])) aria-disabled="true" @endif>Download</a>
                                        <button type="button" data-resource-review-open data-review-action="{{ route('studyhub.student.resources.reviews.store', $resource['id']) }}" data-review-resource="{{ $resource['name'] }}" data-review-accuracy="{{ $resource['viewer_review']['accuracy_rating'] ?? '' }}" data-review-clarity="{{ $resource['viewer_review']['clarity_rating'] ?? '' }}" data-review-usefulness="{{ $resource['viewer_review']['usefulness_rating'] ?? '' }}" data-review-text="{{ $resource['viewer_review']['review_text'] ?? '' }}">Rate</button>
                                        @if (! empty($resource['can_delete']))
                                            <form method="POST" action="{{ route('studyhub.student.resources.delete', $resource['id']) }}">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="redirect_to" value="{{ route('studyhub.student.resources') }}">
                                                <button type="button" data-resource-delete-open data-resource-delete-filename="{{ $resource['name'] }}">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="resources-empty-state app-empty-state is-visible">
                            <span class="app-empty-icon">{!! $icons['file'] !!}</span>
                            <strong>{{ $hasActiveFilters ? 'No resources match your filters' : 'No resources yet' }}</strong>
                            <span>{{ $hasActiveFilters ? 'Try fewer filters or a broader file search.' : 'Files uploaded to your joined groups will appear here.' }}</span>
                        </div>
                    @endif

                    <div class="resources-empty-state app-empty-state hidden" data-resource-live-empty>
                        <span class="app-empty-icon">{!! $icons['search'] !!}</span>
                        <strong>No resources match your search</strong>
                        <span>Try a file name, group, category, or uploader.</span>
                    </div>

                    @if (method_exists($resources, 'links'))
                        <div class="resource-pagination" data-resource-pagination>
                            {{ $resources->links() }}
                        </div>
                    @endif
                </section>
            </main>

            <aside class="resource-side-panel">
                <section class="resource-side-card">
                    <h3>Library Overview</h3>
                    <dl class="resource-overview-list">
                        <div><dt>{!! $icons['file'] !!}<span>Total Files</span></dt><dd>{{ $overview['total_files'] }}</dd></div>
                        <div><dt>{!! $icons['library'] !!}<span>Folders</span></dt><dd>{{ $overview['folders'] }}</dd></div>
                        <div><dt>{!! $icons['download'] !!}<span>Total Size</span></dt><dd>{{ $overview['total_size'] }}</dd></div>
                        <div><dt>{!! $icons['library'] !!}<span>Favorites</span></dt><dd>{{ $overview['favorites'] }}</dd></div>
                    </dl>
                </section>

                <section class="resource-side-card resource-upload-card">
                    <button class="resource-upload-card-trigger" type="button" data-resource-upload-open>
                        <span class="icon-box">{!! $icons['upload-cloud'] ?? $icons['plus'] !!}</span>
                        <strong>Upload File</strong>
                        <small>Drag, drop, or choose a file</small>
                    </button>
                </section>

                <section class="resource-side-card">
                    <h3>Most Used Categories</h3>
                    <div class="resource-category-list">
                        @forelse ($resourceCategoryUsage ?? [] as $categoryUsage)
                            <a href="{{ route('studyhub.student.resources', ['category' => $categoryUsage['name']]) }}">
                                <span>{!! $icons['file'] !!}{{ $categoryUsage['name'] }}</span>
                                <strong>{{ $categoryUsage['count'] }}</strong>
                            </a>
                        @empty
                            <p>No categories yet.</p>
                        @endforelse
                    </div>
                    <a class="resource-all-categories-link" href="{{ route('studyhub.student.resources') }}">View all categories</a>
                </section>
            </aside>
        </div>
    </div>

    <x-studyhub.modal
        title="Upload file"
        subtitle="Add a file to one of your joined groups."
        close-data="data-resource-upload-close"
        :open="$errors->any()"
        data-resource-upload-modal
    >
        @if ($errors->any())
            <div class="resources-errors" role="alert" aria-live="polite">
                <strong>Upload was not saved</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="grid gap-3" method="POST" action="{{ route('studyhub.student.resources.store') }}" enctype="multipart/form-data">
            @csrf
            <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                <span class="text-sm font-extrabold text-[#244231]">Study group</span>
                <select class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" name="group_id" required>
                    <option value="">Choose group</option>
                    @foreach ($uploadGroups as $group)
                        <option value="{{ $group['id'] }}" @selected((string) old('group_id') === (string) $group['id'])>{{ $group['name'] }}</option>
                    @endforeach
                </select>
            </label>

            <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                <span class="text-sm font-extrabold text-[#244231]">Category</span>
                <select class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" name="category" required>
                    <option value="">Choose category</option>
                    @foreach (array_slice($categories, 1) as $category)
                        <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                    @endforeach
                </select>
            </label>

            <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                <span class="text-sm font-extrabold text-[#244231]">File</span>
                <span class="upload-dropzone" data-upload-dropzone>
                    <input class="upload-dropzone-input" type="file" name="resource_file" required data-upload-file-input>
                    <span class="upload-dropzone-icon">{!! $icons['file'] !!}</span>
                    <span class="upload-dropzone-title">Drag file here</span>
                    <span class="upload-dropzone-subtitle">or click to choose</span>
                    <span class="upload-dropzone-name" data-upload-file-name>No file selected</span>
                </span>
            </label>

            <div class="sticky bottom-0 -mx-5 mt-1 flex justify-end border-t border-emerald-100 bg-white/90 px-5 py-4 backdrop-blur sm:-mx-6 sm:px-6">
                <button class="min-h-[54px] w-full rounded-2xl bg-emerald-500 px-6 font-extrabold text-white shadow-[0_14px_28px_rgba(73,182,112,0.22)] transition hover:bg-emerald-600 sm:w-auto sm:min-w-[180px]" type="submit" data-loading-label="Uploading...">Upload File</button>
            </div>
        </form>
    </x-studyhub.modal>

    <x-studyhub.modal
        title="Delete resource?"
        subtitle="This removes the file from the shared resource library."
        close-data="data-resource-delete-close"
        size="sm"
        data-resource-delete-modal
    >
        <div class="resource-delete-dialog">
            <div class="resource-delete-dialog-icon">
                <span class="icon-box">{!! $icons['trash'] !!}</span>
            </div>
            <div class="resource-delete-dialog-copy">
                <strong data-resource-delete-target>this resource</strong>
                <span>Deleting this resource also removes its stored file. This action cannot be undone.</span>
            </div>
        </div>

        <div class="resource-delete-dialog-actions">
            <button class="resource-delete-cancel" type="button" data-resource-delete-close>Cancel</button>
            <button class="resource-delete-confirm" type="button" data-resource-delete-confirm>
                <span class="icon-box">{!! $icons['trash'] !!}</span>
                <span>Delete Resource</span>
            </button>
        </div>
    </x-studyhub.modal>

    <x-studyhub.modal
        title="Rate resource"
        subtitle="Score accuracy, clarity, and usefulness for classmates."
        close-data="data-resource-review-close"
        size="lg"
        data-resource-review-modal
    >
        <form class="resource-review-form" method="POST" action="#" data-resource-review-form>
            @csrf
            <input type="hidden" name="redirect_to" value="{{ $reviewRedirectUrl }}">

            <div class="resource-review-modal-head">
                <span class="resource-review-modal-mark">A+</span>
                <div>
                    <strong data-resource-review-name>Selected resource</strong>
                    <span>Your review helps the strongest materials rise first.</span>
                </div>
            </div>

            @foreach ([
                'accuracy_rating' => ['label' => 'Accuracy', 'hint' => 'Facts, formulas, and answers are reliable.'],
                'clarity_rating' => ['label' => 'Clarity', 'hint' => 'Ideas are easy to follow and well organized.'],
                'usefulness_rating' => ['label' => 'Usefulness', 'hint' => 'This actually helps someone study faster.'],
            ] as $field => $ratingCopy)
                <fieldset class="resource-rating-field" data-rating-field="{{ $field }}">
                    <div>
                        <legend>{{ $ratingCopy['label'] }}</legend>
                        <p>{{ $ratingCopy['hint'] }}</p>
                    </div>
                    <div class="resource-rating-options">
                        @for ($rating = 1; $rating <= 5; $rating++)
                            <label>
                                <input type="radio" name="{{ $field }}" value="{{ $rating }}" required>
                                <span>{{ $rating }}</span>
                            </label>
                        @endfor
                    </div>
                </fieldset>
            @endforeach

            <label class="resource-review-text">
                <span>Short review</span>
                <textarea name="review_text" rows="4" maxlength="600" placeholder="What made this resource accurate, clear, or useful?" data-resource-review-text></textarea>
            </label>

            <div class="resource-review-actions">
                <button class="resource-review-cancel" type="button" data-resource-review-close>Cancel</button>
                <button class="resource-review-submit" type="submit" data-loading-label="Saving review...">Save Review</button>
            </div>
        </form>
    </x-studyhub.modal>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.querySelector('[data-resource-upload-modal]');
            const deleteModal = document.querySelector('[data-resource-delete-modal]');
            const reviewModal = document.querySelector('[data-resource-review-modal]');
            const reviewForm = document.querySelector('[data-resource-review-form]');
            const reviewName = document.querySelector('[data-resource-review-name]');
            const reviewText = document.querySelector('[data-resource-review-text]');
            const deleteName = document.querySelector('[data-resource-delete-target]');
            const deleteConfirm = document.querySelector('[data-resource-delete-confirm]');
            const filterForm = document.querySelector('[data-resource-filter-form]');
            const searchInput = document.querySelector('[data-resource-search]');
            const autoSubmitControls = filterForm ? Array.from(filterForm.querySelectorAll('[data-resource-auto-submit]')) : [];
            const uploadDropzones = document.querySelectorAll('[data-upload-dropzone]');
            const filesView = document.querySelector('[data-resource-files-view]');
            const resourceCards = Array.from(document.querySelectorAll('[data-resource-file-card]'));
            const resourceLiveEmpty = document.querySelector('[data-resource-live-empty]');
            const resourcePagination = document.querySelector('[data-resource-pagination]');
            const viewButtons = Array.from(document.querySelectorAll('[data-resource-view-toggle]'));
            let pendingDeleteForm = null;
            let filterSubmitTimer = null;

            if (modal) {
                window.StudyHubUI.bindModalTriggers({
                    modal: modal,
                    open: '[data-resource-upload-open]',
                    close: '[data-resource-upload-close]',
                });
            }

            if (deleteModal) {
                window.StudyHubUI.bindModalTriggers({
                    modal: deleteModal,
                    open: '[data-resource-delete-open]',
                    close: '[data-resource-delete-close]',
                    beforeOpen: function (button) {
                        pendingDeleteForm = button.closest('form');

                        if (deleteName) {
                            deleteName.textContent = button.dataset.resourceDeleteFilename || 'this resource';
                        }
                    },
                    afterClose: function () {
                        pendingDeleteForm = null;
                    },
                });

                deleteConfirm?.addEventListener('click', function () {
                    if (! pendingDeleteForm) {
                        window.StudyHubUI.setModalState(deleteModal, false);
                        return;
                    }

                    deleteConfirm.disabled = true;
                    deleteConfirm.innerHTML = '<span class="student-button-spinner" aria-hidden="true"></span><span>Deleting...</span>';
                    pendingDeleteForm.submit();
                });
            }

            const setReviewRating = function (field, value) {
                if (! reviewForm) {
                    return;
                }

                reviewForm.querySelectorAll('input[name="' + field + '"]').forEach(function (input) {
                    input.checked = value !== '' && input.value === String(value);
                });
            };

            if (reviewModal) {
                window.StudyHubUI.bindModalTriggers({
                    modal: reviewModal,
                    open: '[data-resource-review-open]',
                    close: '[data-resource-review-close]',
                    beforeOpen: function (button) {
                        if (reviewForm) {
                            reviewForm.action = button.dataset.reviewAction || '#';
                        }

                        if (reviewName) {
                            reviewName.textContent = button.dataset.reviewResource || 'Selected resource';
                        }

                        setReviewRating('accuracy_rating', button.dataset.reviewAccuracy || '');
                        setReviewRating('clarity_rating', button.dataset.reviewClarity || '');
                        setReviewRating('usefulness_rating', button.dataset.reviewUsefulness || '');

                        if (reviewText) {
                            reviewText.value = button.dataset.reviewText || '';
                        }
                    },
                });
            }

            autoSubmitControls.forEach(function (control) {
                control.addEventListener('change', function () {
                    window.clearTimeout(filterSubmitTimer);
                    filterSubmitTimer = window.setTimeout(function () {
                        if (filterForm?.requestSubmit) {
                            filterForm.requestSubmit();
                            return;
                        }

                        filterForm?.submit();
                    }, 120);
                });
            });

            const applyResourceLiveSearch = function () {
                const searchTerm = (searchInput?.value || '').trim().toLowerCase();
                let visibleCount = 0;

                resourceCards.forEach(function (card) {
                    const isVisible = searchTerm === '' || (card.dataset.resourceSearchText || '').includes(searchTerm);
                    card.classList.toggle('is-hidden', ! isVisible);

                    if (isVisible) {
                        visibleCount += 1;
                    }
                });

                resourceLiveEmpty?.classList.toggle('hidden', searchTerm === '' || visibleCount > 0);
                resourcePagination?.classList.toggle('is-hidden', searchTerm !== '');
            };

            searchInput?.addEventListener('input', applyResourceLiveSearch);
            searchInput?.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                }
            });
            applyResourceLiveSearch();

            const setResourceViewMode = function (mode) {
                const normalizedMode = mode === 'list' ? 'list' : 'grid';

                filesView?.classList.toggle('is-list-view', normalizedMode === 'list');
                viewButtons.forEach(function (button) {
                    const isActive = button.dataset.resourceViewToggle === normalizedMode;
                    button.classList.toggle('active', isActive);
                    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });

                try {
                    localStorage.setItem('studyhub-resource-view-mode', normalizedMode);
                } catch (error) {
                    // The selected view still works for this page load when storage is unavailable.
                }
            };

            viewButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    setResourceViewMode(button.dataset.resourceViewToggle || 'grid');
                });
            });

            try {
                setResourceViewMode(localStorage.getItem('studyhub-resource-view-mode') || 'grid');
            } catch (error) {
                setResourceViewMode('grid');
            }

            uploadDropzones.forEach(function (dropzone) {
                const input = dropzone.querySelector('[data-upload-file-input]');
                const fileName = dropzone.querySelector('[data-upload-file-name]');

                if (! input || ! fileName) {
                    return;
                }

                const setFileName = function () {
                    fileName.textContent = input.files?.[0]?.name || 'No file selected';
                    dropzone.classList.toggle('has-file', Boolean(input.files?.length));
                };

                input.addEventListener('change', setFileName);

                ['dragenter', 'dragover'].forEach(function (eventName) {
                    dropzone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        dropzone.classList.add('is-dragging');
                    });
                });

                ['dragleave', 'drop'].forEach(function (eventName) {
                    dropzone.addEventListener(eventName, function (event) {
                        event.preventDefault();
                        dropzone.classList.remove('is-dragging');
                    });
                });

                dropzone.addEventListener('drop', function (event) {
                    if (event.dataTransfer?.files?.length) {
                        input.files = event.dataTransfer.files;
                        setFileName();
                    }
                });
            });

            window.StudyHubUI.syncBodyOverflow();
        });
    </script>
@endsection
