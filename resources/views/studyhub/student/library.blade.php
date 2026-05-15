@extends('studyhub.student.layout')

@section('title', 'My Library')

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

        if (in_array($key, ['ppt', 'pptx'], true) || str_contains($name, 'marketing')) {
            return ['rgb' => '249, 115, 22', 'soft' => '255, 237, 213', 'text' => '#ea580c'];
        }

        if (in_array($key, ['xls', 'xlsx'], true) || str_contains($name, 'data')) {
            return ['rgb' => '34, 197, 94', 'soft' => '220, 252, 231', 'text' => '#16a34a'];
        }

        if (str_contains($category, 'guide') || str_contains($name, 'formula')) {
            return ['rgb' => '168, 85, 247', 'soft' => '243, 232, 255', 'text' => '#9333ea'];
        }

        return ['rgb' => '245, 158, 11', 'soft' => '254, 243, 199', 'text' => '#d97706'];
    };

    $savedTotal = method_exists($savedResources, 'total') ? $savedResources->total() : count($savedResources);
    $savedItems = collect(method_exists($savedResources, 'items') ? $savedResources->items() : $savedResources);
    $continueItems = $savedItems->take(4);
    $previewResource = $savedItems->first() ?: (count($recentResources) > 0 ? $recentResources[0] : null);
    $selectedFolderValue = $selectedFolder['type'] === 'folder' ? $selectedFolder['id'] : ($selectedFolder['type'] === 'unfiled' ? 'unfiled' : '');
    $libraryFilters = $libraryFilters ?? [
        'file_type' => '',
        'sort' => 'newest',
        'availability' => 'all',
        'item' => 'all',
    ];
    $libraryQueryBase = array_filter([
        'q' => $librarySearch ?: null,
        'folder' => $selectedFolderValue ?: null,
        'file_type' => $libraryFilters['file_type'] ?: null,
        'sort' => $libraryFilters['sort'] !== 'newest' ? $libraryFilters['sort'] : null,
        'availability' => $libraryFilters['availability'] !== 'all' ? $libraryFilters['availability'] : null,
    ], fn ($value) => $value !== null && $value !== '');
@endphp

@section('page')
    <div class="library-page">
        <div class="library-top-tools">
            <form class="library-global-search" method="GET" action="{{ route('studyhub.student.library') }}" data-library-search-form>
                @if ($selectedFolder['type'] === 'folder')
                    <input type="hidden" name="folder" value="{{ $selectedFolder['id'] }}">
                @elseif ($selectedFolder['type'] === 'unfiled')
                    <input type="hidden" name="folder" value="unfiled">
                @endif
                <span class="icon-box">{!! $icons['search'] !!}</span>
                <input type="search" name="q" value="{{ $librarySearch }}" placeholder="Search files, notes, PDFs..." data-library-search>
            </form>
        </div>

        <nav class="file-scope-switch" aria-label="File library views">
            <a href="{{ route('studyhub.student.resources') }}">All Resources</a>
            <a class="active" href="{{ route('studyhub.student.library') }}">My Library</a>
        </nav>

        <div class="library-dashboard">
            <main class="library-content-panel">
                <header class="library-hero">
                    <div>
                        <h2 class="page-title">My Library</h2>
                        <p class="page-subtitle">Manage your saved study materials and personal resources.</p>
                    </div>
                    <div class="library-hero-actions">
                        <button class="library-primary-button" type="button" data-library-upload-open>
                            <span class="icon-box">{!! $icons['plus'] !!}</span>
                            <span>Upload File</span>
                        </button>
                        <button class="library-light-button" type="button" data-library-folder-open>
                            <span class="icon-box">{!! $icons['plus'] !!}</span>
                            <span>New Folder</span>
                        </button>
                    </div>
                </header>

                <form class="library-filter-bar" method="GET" action="{{ route('studyhub.student.library') }}" data-library-filter-form>
                    <label class="library-inline-search">
                        <input type="search" name="q" value="{{ $librarySearch }}" placeholder="Search in library..." data-library-search>
                        <span class="icon-box">{!! $icons['search'] !!}</span>
                    </label>
                    <select name="folder" aria-label="Filter by folder" data-library-auto-submit>
                        <option value="" @selected($selectedFolderValue === '')>All folders</option>
                        <option value="unfiled" @selected($selectedFolderValue === 'unfiled')>Unfiled</option>
                        @foreach ($folderOptions as $folderOption)
                            <option value="{{ $folderOption['id'] }}" @selected((string) $selectedFolderValue === (string) $folderOption['id'])>{{ $folderOption['name'] }}</option>
                        @endforeach
                    </select>
                    <select name="file_type" aria-label="File type" data-library-auto-submit>
                        <option value="">All file types</option>
                        @foreach ($libraryFileTypes as $fileType)
                            <option value="{{ $fileType }}" @selected($libraryFilters['file_type'] === $fileType)>{{ strtoupper($fileType) }}</option>
                        @endforeach
                    </select>
                    <select name="sort" aria-label="Date added" data-library-auto-submit>
                        <option value="newest" @selected($libraryFilters['sort'] === 'newest')>Newest first</option>
                        <option value="oldest" @selected($libraryFilters['sort'] === 'oldest')>Oldest first</option>
                        <option value="name" @selected($libraryFilters['sort'] === 'name')>Name A-Z</option>
                    </select>
                    <select name="availability" aria-label="Availability" data-library-auto-submit>
                        <option value="all" @selected($libraryFilters['availability'] === 'all')>All items</option>
                        <option value="downloadable" @selected($libraryFilters['availability'] === 'downloadable')>Downloadable</option>
                    </select>
                    <input type="hidden" name="item" value="{{ $libraryFilters['item'] }}">
                    <button class="library-view-toggle active" type="button" aria-label="Grid view" data-library-grid-view>
                        <span></span><span></span><span></span><span></span>
                    </button>
                    <button class="library-view-toggle" type="button" aria-label="List view" data-library-list-view>
                        <span></span><span></span><span></span>
                    </button>
                </form>

                <nav class="library-tab-strip" aria-label="Library filters">
                    <a class="{{ $libraryFilters['item'] === 'all' && $libraryFilters['availability'] === 'all' ? 'active' : '' }}" href="{{ route('studyhub.student.library', array_filter(array_merge($libraryQueryBase, ['item' => null, 'availability' => null]), fn ($value) => $value !== null && $value !== '')) }}">{!! $icons['library'] !!}<span>Saved</span></a>
                    <a class="{{ $libraryFilters['item'] === 'uploaded' ? 'active' : '' }}" href="{{ route('studyhub.student.library', array_filter(array_merge($libraryQueryBase, ['item' => 'uploaded', 'availability' => null]), fn ($value) => $value !== null && $value !== '')) }}">{!! $icons['upload-cloud'] ?? $icons['plus'] !!}<span>Uploaded</span></a>
                    <a class="{{ $libraryFilters['item'] === 'recent' ? 'active' : '' }}" href="{{ route('studyhub.student.library', array_filter(array_merge($libraryQueryBase, ['item' => 'recent']), fn ($value) => $value !== null && $value !== '')) }}">{!! $icons['clock'] !!}<span>Recent</span></a>
                </nav>

                <section class="library-section library-continue-section">
                    <div class="library-section-head">
                        <h3>Continue Studying</h3>
                        <a href="#library-all-files">View all</a>
                    </div>

                    @if ($continueItems->isNotEmpty())
                        <div class="library-continue-grid">
                            @foreach ($continueItems as $resource)
                                @php
                                    $extension = strtoupper($resource['file_type'] ?: pathinfo($resource['name'], PATHINFO_EXTENSION) ?: 'FILE');
                                    $resourceTheme = $resourceThemeFor($resource, $extension);
                                @endphp
                                <article class="library-study-card" style="--resource-rgb: {{ $resourceTheme['rgb'] }}; --resource-soft-rgb: {{ $resourceTheme['soft'] }}; --resource-text: {{ $resourceTheme['text'] }};">
                                    <div class="library-file-badge">
                                        <span class="icon-box">{!! $icons['file'] !!}</span>
                                        <strong>{{ $extension }}</strong>
                                    </div>
                                    <h4>{{ $resource['name'] }}</h4>
                                    <p>{{ $resource['size'] }} &middot; {{ $extension }}</p>
                                    <small>Last opened {{ $resource['library_saved_at'] }}</small>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="library-empty-state compact">Save resources from the Resource Library to start studying from here.</div>
                    @endif
                </section>

                <section class="library-section" id="library-all-files">
                    <div class="library-section-head">
                        <div>
                            <h3>All Files</h3>
                            <span>{{ $savedTotal }} items</span>
                        </div>
                        <span class="library-sort-chip">Sort by: Newest</span>
                    </div>

                    @if ($savedItems->isNotEmpty())
                        <div class="library-files-grid" data-library-files-grid>
                            @foreach ($savedItems as $resource)
                                @php
                                    $extension = strtoupper($resource['file_type'] ?: pathinfo($resource['name'], PATHINFO_EXTENSION) ?: 'FILE');
                                    $resourceTheme = $resourceThemeFor($resource, $extension);
                                    $librarySearchText = strtolower(implode(' ', [
                                        $resource['name'] ?? '',
                                        $resource['group'] ?? '',
                                        $resource['category'] ?? '',
                                        $resource['uploaded_by'] ?? '',
                                        $resource['saved_folder'] ?? '',
                                        $extension,
                                    ]));
                                @endphp
                                <article class="library-file-card" style="--resource-rgb: {{ $resourceTheme['rgb'] }}; --resource-soft-rgb: {{ $resourceTheme['soft'] }}; --resource-text: {{ $resourceTheme['text'] }};" data-library-file-card data-library-search-text="{{ $librarySearchText }}">
                                    <div class="library-file-card-top">
                                        <span class="icon-box library-file-icon">{!! $icons['file'] !!}</span>
                                        <span class="library-file-type">{{ $extension }}</span>
                                    </div>
                                    <h4>{{ $resource['name'] }}</h4>
                                    <p>{{ $resource['size'] }} &middot; {{ $extension }}</p>
                                    <small>Added {{ $resource['library_saved_at'] }}</small>
                                    <form class="library-move-form" method="POST" action="{{ route('studyhub.student.library.saved.update', $resource['library_saved_id']) }}">
                                        @csrf
                                        @method('PATCH')
                                        <select name="resource_folder_id" aria-label="Move {{ $resource['name'] }} to folder" onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()">
                                            <option value="">Unfiled</option>
                                            @foreach ($folderOptions as $folderOption)
                                                <option value="{{ $folderOption['id'] }}" @selected((string) $resource['library_folder_id'] === (string) $folderOption['id'])>{{ $folderOption['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                    <div class="library-card-actions">
                                        <a class="{{ empty($resource['path']) ? 'is-disabled' : '' }}" href="{{ ! empty($resource['path']) ? route('studyhub.student.resources.view', $resource['id']) : '#' }}" target="_blank" rel="noopener" @if (empty($resource['path'])) aria-disabled="true" @endif>Open</a>
                                        <a class="{{ empty($resource['path']) ? 'is-disabled' : '' }}" href="{{ ! empty($resource['path']) ? route('studyhub.student.resources.download', $resource['id']) : '#' }}" @if (empty($resource['path'])) aria-disabled="true" @endif>Download</a>
                                        <form method="POST" action="{{ route('studyhub.student.resources.unsave', $resource['id']) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" aria-label="Remove {{ $resource['name'] }}">Remove</button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="library-empty-state">
                            <span class="icon-box">{!! $icons['library'] !!}</span>
                            <strong>{{ $librarySearch !== '' ? 'No saved resources match your search' : 'Your library is ready' }}</strong>
                            <p>{{ $librarySearch !== '' ? 'Try a broader search or open another folder.' : 'Save resources from the Resource Library and they will appear here.' }}</p>
                            <a href="{{ route('studyhub.student.resources') }}">Browse Resources</a>
                        </div>
                    @endif

                    <div class="library-empty-state hidden" data-library-live-empty>
                        <span class="icon-box">{!! $icons['search'] !!}</span>
                        <strong>No saved resources match your search</strong>
                        <p>Try a file name, group, folder, category, or uploader.</p>
                        <a href="{{ route('studyhub.student.resources') }}">Browse Resources</a>
                    </div>

                    @if (method_exists($savedResources, 'links'))
                        <div class="library-pagination" data-library-pagination>
                            {{ $savedResources->links() }}
                        </div>
                    @endif
                </section>
            </main>

            <aside class="library-side-panel">
                <section class="library-side-card">
                    <h3>Library Overview</h3>
                    <dl class="library-overview-list">
                        <div><dt>Total Files</dt><dd>{{ $libraryStats['saved'] }}</dd></div>
                        <div><dt>Folders</dt><dd>{{ $libraryStats['folders'] }}</dd></div>
                        <div><dt>Total Size</dt><dd>{{ $libraryStats['total_size'] ?? '0 KB' }}</dd></div>
                        <div><dt>Favorites</dt><dd>{{ $libraryStats['saved'] }}</dd></div>
                    </dl>
                </section>

                <section class="library-side-card">
                    <h3>Folders</h3>
                    <nav class="library-folder-list" aria-label="Library folders">
                        <a class="{{ $selectedFolder['type'] === 'all' ? 'active' : '' }}" href="{{ route('studyhub.student.library') }}">
                            <span>All saved</span>
                            <strong>{{ $libraryStats['saved'] }}</strong>
                        </a>
                        <a class="{{ $selectedFolder['type'] === 'unfiled' ? 'active' : '' }}" href="{{ route('studyhub.student.library', ['folder' => 'unfiled']) }}">
                            <span>Unfiled</span>
                            <strong>{{ $libraryStats['unfiled'] }}</strong>
                        </a>
                        @foreach ($folders as $folder)
                            <div class="library-folder-row" style="--folder-color: {{ $folder['color'] }}">
                                <a class="{{ $selectedFolder['type'] === 'folder' && (int) $selectedFolder['id'] === (int) $folder['id'] ? 'active' : '' }}" href="{{ route('studyhub.student.library', ['folder' => $folder['id']]) }}">
                                    <span>{{ $folder['name'] }}</span>
                                    <strong>{{ $folder['saved_count'] }}</strong>
                                </a>
                                <form method="POST" action="{{ route('studyhub.student.library.folders.delete', $folder['id']) }}" onsubmit="return confirm('Delete this folder? Saved resources will stay in your library.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" aria-label="Delete {{ $folder['name'] }} folder">&times;</button>
                                </form>
                            </div>
                        @endforeach
                    </nav>
                </section>

                @if ($previewResource)
                    @php
                        $previewExtension = strtoupper($previewResource['file_type'] ?: pathinfo($previewResource['name'], PATHINFO_EXTENSION) ?: 'FILE');
                        $previewTheme = $resourceThemeFor($previewResource, $previewExtension);
                    @endphp
                    <section class="library-side-card library-preview-card" style="--resource-rgb: {{ $previewTheme['rgb'] }}; --resource-soft-rgb: {{ $previewTheme['soft'] }}; --resource-text: {{ $previewTheme['text'] }};">
                        <h3>Recent File Preview</h3>
                        <div class="library-preview-document">
                            <span class="library-preview-pill">{{ $previewExtension }}</span>
                            <strong>{{ \Illuminate\Support\Str::limit($previewResource['name'], 22) }}</strong>
                            <span></span><span></span><span></span><span></span><span></span>
                            <em>1/45</em>
                        </div>
                        <h4>{{ $previewResource['name'] }}</h4>
                        <p>{{ $previewResource['size'] }} &middot; {{ $previewExtension }}</p>
                        <a class="library-preview-open {{ empty($previewResource['path']) ? 'is-disabled' : '' }}" href="{{ ! empty($previewResource['path']) ? route('studyhub.student.resources.view', $previewResource['id']) : '#' }}" target="_blank" rel="noopener" @if (empty($previewResource['path'])) aria-disabled="true" @endif>Open</a>
                        <div class="library-preview-actions">
                            <a href="{{ route('studyhub.student.resources') }}">{!! $icons['users'] !!}<span>Share</span></a>
                            <a class="{{ empty($previewResource['path']) ? 'is-disabled' : '' }}" href="{{ ! empty($previewResource['path']) ? route('studyhub.student.resources.download', $previewResource['id']) : '#' }}" @if (empty($previewResource['path'])) aria-disabled="true" @endif>{!! $icons['download'] !!}<span>Download</span></a>
                            <a href="#library-all-files">{!! $icons['settings'] !!}<span>More</span></a>
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </div>

    <x-studyhub.modal
        title="Upload file"
        subtitle="Add a study material to one of your joined groups."
        close-data="data-library-upload-close"
        :open="$errors->any() && old('library_upload_intent') === '1'"
        data-library-upload-modal
    >
        @if ($errors->any() && old('library_upload_intent') === '1')
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
            <input type="hidden" name="redirect_to" value="{{ route('studyhub.student.library') }}">
            <input type="hidden" name="library_upload_intent" value="1">

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
                <span class="upload-dropzone" data-library-upload-dropzone>
                    <input class="upload-dropzone-input" type="file" name="resource_file" required data-library-upload-file-input>
                    <span class="upload-dropzone-icon">{!! $icons['file'] !!}</span>
                    <span class="upload-dropzone-title">Drag file here</span>
                    <span class="upload-dropzone-subtitle">or click to choose</span>
                    <span class="upload-dropzone-name" data-library-upload-file-name>No file selected</span>
                </span>
            </label>

            <div class="sticky bottom-0 -mx-5 mt-1 flex justify-end border-t border-emerald-100 bg-white/90 px-5 py-4 backdrop-blur sm:-mx-6 sm:px-6">
                <button class="min-h-[54px] w-full rounded-2xl bg-emerald-500 px-6 font-extrabold text-white shadow-[0_14px_28px_rgba(73,182,112,0.22)] transition hover:bg-emerald-600 sm:w-auto sm:min-w-[180px]" type="submit" data-loading-label="Uploading...">Upload File</button>
            </div>
        </form>
    </x-studyhub.modal>

    <x-studyhub.modal
        title="New folder"
        subtitle="Create a place to organize saved study materials."
        close-data="data-library-folder-close"
        :open="$errors->any() && old('library_folder_intent') === '1'"
        data-library-folder-modal
    >
        @if ($errors->any() && old('library_folder_intent') === '1')
            <div class="resources-errors" role="alert" aria-live="polite">
                <strong>Folder was not created</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="library-folder-modal-form" method="POST" action="{{ route('studyhub.student.library.folders.store') }}">
            @csrf
            <input type="hidden" name="library_folder_intent" value="1">

            <label>
                <span>Folder name</span>
                <input type="text" name="name" maxlength="80" value="{{ old('library_folder_intent') === '1' ? old('name') : '' }}" placeholder="Finals Review" required autofocus>
            </label>

            <div class="library-folder-modal-colors" aria-label="Folder color">
                @foreach ($folderColors as $color)
                    <label style="--folder-color: {{ $color }}">
                        <input type="radio" name="color" value="{{ $color }}" @checked((old('library_folder_intent') === '1' ? old('color') : $folderColors[0]) === $color)>
                        <span></span>
                    </label>
                @endforeach
            </div>

            <div class="library-folder-modal-actions">
                <button class="library-folder-cancel" type="button" data-library-folder-close>Cancel</button>
                <button class="library-folder-submit" type="submit" data-loading-label="Creating...">Create Folder</button>
            </div>
        </form>
    </x-studyhub.modal>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.querySelector('[data-library-upload-modal]');
            const folderModal = document.querySelector('[data-library-folder-modal]');
            const filterForm = document.querySelector('[data-library-filter-form]');
            const searchInputs = Array.from(document.querySelectorAll('[data-library-search]'));
            const grid = document.querySelector('[data-library-files-grid]');
            const libraryCards = Array.from(document.querySelectorAll('[data-library-file-card]'));
            const libraryLiveEmpty = document.querySelector('[data-library-live-empty]');
            const libraryPagination = document.querySelector('[data-library-pagination]');
            const gridButton = document.querySelector('[data-library-grid-view]');
            const listButton = document.querySelector('[data-library-list-view]');
            let librarySearchTimer = null;

            if (modal) {
                window.StudyHubUI.bindModalTriggers({
                    modal: modal,
                    open: '[data-library-upload-open]',
                    close: '[data-library-upload-close]',
                });
            }

            if (folderModal) {
                window.StudyHubUI.bindModalTriggers({
                    modal: folderModal,
                    open: '[data-library-folder-open]',
                    close: '[data-library-folder-close]',
                    afterOpen: function () {
                        folderModal.querySelector('input[name="name"]')?.focus();
                    },
                });
            }

            filterForm?.querySelectorAll('[data-library-auto-submit]').forEach(function (control) {
                control.addEventListener('change', function () {
                    if (filterForm.requestSubmit) {
                        filterForm.requestSubmit();
                        return;
                    }

                    filterForm.submit();
                });
            });

            const applyLibraryLiveSearch = function (sourceInput) {
                const searchTerm = (sourceInput?.value || '').trim().toLowerCase();
                let visibleCount = 0;

                searchInputs.forEach(function (input) {
                    if (input !== sourceInput) {
                        input.value = sourceInput?.value || '';
                    }
                });

                libraryCards.forEach(function (card) {
                    const isVisible = searchTerm === '' || (card.dataset.librarySearchText || '').includes(searchTerm);
                    card.classList.toggle('is-hidden', ! isVisible);

                    if (isVisible) {
                        visibleCount += 1;
                    }
                });

                libraryLiveEmpty?.classList.toggle('hidden', searchTerm === '' || visibleCount > 0);
                libraryPagination?.classList.toggle('is-hidden', searchTerm !== '');
            };

            searchInputs.forEach(function (input) {
                input.addEventListener('input', function () {
                    window.clearTimeout(librarySearchTimer);
                    librarySearchTimer = window.setTimeout(function () {
                        applyLibraryLiveSearch(input);
                    }, 80);
                });

                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                    }
                });
            });

            applyLibraryLiveSearch(searchInputs.find(function (input) {
                return input.value.trim() !== '';
            }) || searchInputs[0]);

            const setLibraryView = function (view) {
                const isList = view === 'list';

                grid?.classList.toggle('is-list', isList);
                gridButton?.classList.toggle('active', ! isList);
                listButton?.classList.toggle('active', isList);

                try {
                    localStorage.setItem('studyhub-library-view', view);
                } catch (error) {
                    // Keep the default grid view when storage is unavailable.
                }
            };

            try {
                setLibraryView(localStorage.getItem('studyhub-library-view') === 'list' ? 'list' : 'grid');
            } catch (error) {
                setLibraryView('grid');
            }

            gridButton?.addEventListener('click', function () {
                setLibraryView('grid');
            });

            listButton?.addEventListener('click', function () {
                setLibraryView('list');
            });

            document.querySelectorAll('[data-library-upload-dropzone]').forEach(function (dropzone) {
                const input = dropzone.querySelector('[data-library-upload-file-input]');
                const fileName = dropzone.querySelector('[data-library-upload-file-name]');

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
        });
    </script>
@endsection
