@extends('studyhub.student.layout')

@section('title', 'Resource Library')

@php
    $resourceThemeFor = function (array $resource, string $extension): array {
        $key = strtolower($extension);
        $category = strtolower($resource['category'] ?? '');
        $name = strtolower($resource['name'] ?? '');

        if (str_contains($category, 'lecture') || in_array($key, ['pdf'], true)) {
            return ['rgb' => '59, 130, 246', 'soft' => '219, 234, 254', 'text' => '#1d4ed8'];
        }

        if (str_contains($category, 'guide') || str_contains($name, 'data') || str_contains($name, 'cheat')) {
            return ['rgb' => '139, 92, 246', 'soft' => '237, 233, 254', 'text' => '#6d28d9'];
        }

        if (in_array($key, ['doc', 'docx'], true) || str_contains($category, 'assignment')) {
            return ['rgb' => '245, 158, 11', 'soft' => '254, 243, 199', 'text' => '#b45309'];
        }

        return ['rgb' => '34, 197, 94', 'soft' => '220, 252, 231', 'text' => '#15803d'];
    };
@endphp

@section('page')
    <div class="resources-header">
        <h2 class="page-title">Resource Library</h2>
        <p class="page-subtitle">Access all your study materials in one place</p>
    </div>

    <div class="resources-toolbar">
        <div class="search-box">
            <span class="icon-box">{!! $icons['search'] !!}</span>
            <input type="text" placeholder="Search resources..." data-resource-search>
        </div>
        <select class="resources-filter-select" data-resource-category>
            @foreach ($categories as $category)
                <option value="{{ strtolower($category) }}">{{ $category === 'All' ? 'All Categories' : $category }}</option>
            @endforeach
        </select>
        <select class="resources-filter-select" data-resource-group>
            <option value="">All Groups</option>
            @foreach (collect($resources)->pluck('group')->filter()->unique()->sort()->values() as $groupName)
                <option value="{{ strtolower($groupName) }}">{{ $groupName }}</option>
            @endforeach
        </select>
        <select class="resources-filter-select" data-resource-downloadable>
            <option value="">All Files</option>
            <option value="downloadable">Downloadable Only</option>
            <option value="unavailable">Unavailable Files</option>
        </select>
        <button class="action-button upload-button" type="button" data-resource-upload-open>
            <span class="icon-box">{!! $icons['plus'] !!}</span>
            <span>Create Resource</span>
        </button>
    </div>

    <section class="resource-grid">
        @foreach ($resources as $resource)
            @php
                $extension = strtoupper(pathinfo($resource['name'], PATHINFO_EXTENSION) ?: 'FILE');
                $resourceTheme = $resourceThemeFor($resource, $extension);
            @endphp
            <article
                class="content-card resource-card"
                style="--resource-rgb: {{ $resourceTheme['rgb'] }}; --resource-soft-rgb: {{ $resourceTheme['soft'] }}; --resource-text: {{ $resourceTheme['text'] }};"
                data-resource-card
                data-name="{{ strtolower($resource['name']) }}"
                data-group="{{ strtolower($resource['group']) }}"
                data-category="{{ strtolower($resource['category']) }}"
                data-author="{{ strtolower($resource['uploaded_by'] ?? 'studyhub member') }}"
                data-downloadable="{{ ! empty($resource['path']) ? 'yes' : 'no' }}"
            >
                <div class="resource-card-accent" aria-hidden="true"></div>
                <div class="resource-card-top">
                    <div class="icon-box resource-icon">{!! $icons['file'] !!}</div>
                    <div class="resource-copy">
                        <div class="resource-title-row">
                            <h3>{{ $resource['name'] }}</h3>
                            <span class="resource-extension">{{ $extension }}</span>
                        </div>
                        <div class="resource-group">{{ $resource['group'] }}</div>
                    </div>
                </div>

                <div class="resource-detail-row">
                    <span class="resource-uploader">
                        <span class="resource-uploader-avatar">
                            @if (! empty($resource['uploader_avatar_url']))
                                <img src="{{ $resource['uploader_avatar_url'] }}" alt="{{ $resource['uploaded_by'] ?? 'StudyHub Member' }}">
                            @else
                                {{ $resource['uploader_initials'] ?? 'SM' }}
                            @endif
                        </span>
                        <span class="resource-uploader-copy">
                            <span>Uploaded by</span>
                            <strong>{{ $resource['uploaded_by'] ?? 'StudyHub Member' }}</strong>
                        </span>
                    </span>
                    <span class="resource-date">{{ $resource['date'] }}</span>
                </div>

                <div class="resource-meta">
                    <span class="resource-tag">{{ $resource['category'] }}</span>
                    <span class="resource-size">{{ $resource['size'] }}</span>
                </div>

                <a class="action-button download-button {{ empty($resource['path']) ? 'is-disabled' : '' }}" href="{{ ! empty($resource['path']) ? route('studyhub.student.resources.download', $resource['id']) : '#' }}" @if (empty($resource['path'])) aria-disabled="true" @endif>
                    <span class="icon-box">{!! $icons['download'] !!}</span>
                    <span>{{ ! empty($resource['path']) ? 'Download' : 'Unavailable' }}</span>
                </a>
            </article>
        @endforeach
    </section>

    @if (method_exists($resources, 'links'))
        <div class="mt-6">
            {{ $resources->links() }}
        </div>
    @endif

    <div class="resources-empty-state app-empty-state hidden" data-resources-empty>
        <span class="app-empty-icon">{!! $icons['file'] !!}</span>
        <strong>No resources found</strong>
        <span>Try another filter or upload a file for your group.</span>
        <button class="app-empty-action" type="button" data-resource-upload-open>Create resource</button>
    </div>

    <x-studyhub.modal
        title="Create resource"
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
                        <button class="min-h-[54px] w-full rounded-2xl bg-emerald-500 px-6 font-extrabold text-white shadow-[0_14px_28px_rgba(73,182,112,0.22)] transition hover:bg-emerald-600 sm:w-auto sm:min-w-[180px]" type="submit" data-loading-label="Creating resource...">Create Resource</button>
                    </div>
                </form>
    </x-studyhub.modal>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.querySelector('[data-resource-upload-modal]');
            const openButton = document.querySelector('[data-resource-upload-open]');
            const closeButtons = document.querySelectorAll('[data-resource-upload-close]');
            const searchInput = document.querySelector('[data-resource-search]');
            const categorySelect = document.querySelector('[data-resource-category]');
            const groupSelect = document.querySelector('[data-resource-group]');
            const downloadableSelect = document.querySelector('[data-resource-downloadable]');
            const resourceCards = Array.from(document.querySelectorAll('[data-resource-card]'));
            const emptyState = document.querySelector('[data-resources-empty]');
            const uploadDropzones = document.querySelectorAll('[data-upload-dropzone]');

            if (! modal || ! openButton) {
                return;
            }

            const setModalState = function (isOpen) {
                modal.classList.toggle('is-open', isOpen);
                document.body.classList.toggle('overflow-hidden', isOpen);
            };

            openButton.addEventListener('click', function () {
                setModalState(true);
            });

            closeButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    setModalState(false);
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                    setModalState(false);
                }
            });

            const applyResourceFilters = function () {
                const searchTerm = (searchInput?.value || '').trim().toLowerCase();
                const selectedCategory = (categorySelect?.value || 'all').trim().toLowerCase();
                const selectedGroup = (groupSelect?.value || '').trim().toLowerCase();
                const selectedDownloadable = (downloadableSelect?.value || '').trim().toLowerCase();
                let visibleCount = 0;

                resourceCards.forEach(function (card) {
                    const matchesSearch = searchTerm === ''
                        || card.dataset.name.includes(searchTerm)
                        || card.dataset.group.includes(searchTerm)
                        || card.dataset.category.includes(searchTerm)
                        || card.dataset.author.includes(searchTerm);
                    const matchesCategory = selectedCategory === 'all' || card.dataset.category === selectedCategory;
                    const matchesGroup = selectedGroup === '' || card.dataset.group === selectedGroup;
                    const matchesDownloadable = selectedDownloadable === ''
                        || (selectedDownloadable === 'downloadable' && card.dataset.downloadable === 'yes')
                        || (selectedDownloadable === 'unavailable' && card.dataset.downloadable === 'no');

                    const isVisible = matchesSearch && matchesCategory && matchesGroup && matchesDownloadable;
                    card.classList.toggle('is-hidden', !isVisible);

                    if (isVisible) {
                        visibleCount += 1;
                    }
                });

                if (emptyState) {
                    emptyState.classList.toggle('hidden', visibleCount !== 0);
                }
            };

            searchInput?.addEventListener('input', applyResourceFilters);
            categorySelect?.addEventListener('change', applyResourceFilters);
            groupSelect?.addEventListener('change', applyResourceFilters);
            downloadableSelect?.addEventListener('change', applyResourceFilters);

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

            applyResourceFilters();

            if (modal.classList.contains('is-open')) {
                document.body.classList.add('overflow-hidden');
            }
        });
    </script>
@endsection
