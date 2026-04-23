@extends('studyhub.student.layout')

@section('title', 'Resource Library')

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
        <button class="secondary-button filter-button" type="button" data-resource-filter-toggle>Filter</button>
        <button class="action-button upload-button" type="button" data-resource-upload-open>
            <span class="icon-box">{!! $icons['plus'] !!}</span>
            <span>Upload Resource</span>
        </button>
    </div>

    <div class="resources-filter-panel" data-resource-filter-panel>
        <select class="resources-filter-select" data-resource-group>
            <option value="">All Groups</option>
            @foreach (collect($resources)->pluck('group')->filter()->unique()->sort()->values() as $groupName)
                <option value="{{ $groupName }}">{{ $groupName }}</option>
            @endforeach
        </select>

        <select class="resources-filter-select" data-resource-downloadable>
            <option value="">All Files</option>
            <option value="downloadable">Downloadable Only</option>
            <option value="unavailable">Unavailable Files</option>
        </select>
    </div>

    <div class="category-row">
        @foreach ($categories as $index => $category)
            <button class="category-pill {{ $index === 0 ? 'active' : '' }}" type="button" data-resource-category="{{ strtolower($category) }}">
                {{ $category }}
            </button>
        @endforeach
    </div>

    <section class="resource-grid">
        @foreach ($resources as $resource)
            <article
                class="content-card resource-card"
                data-resource-card
                data-name="{{ strtolower($resource['name']) }}"
                data-group="{{ strtolower($resource['group']) }}"
                data-category="{{ strtolower($resource['category']) }}"
                data-author="{{ strtolower($resource['uploaded_by'] ?? 'studyhub member') }}"
                data-downloadable="{{ ! empty($resource['path']) ? 'yes' : 'no' }}"
            >
                <div class="resource-card-top">
                    <div class="icon-box resource-icon">{!! $icons['file'] !!}</div>
                    <div class="resource-copy">
                        <h3>{{ $resource['name'] }}</h3>
                        <div class="resource-group">{{ $resource['group'] }}</div>
                        <div class="resource-author">by {{ $resource['uploaded_by'] ?? 'StudyHub Member' }}</div>
                        <div class="resource-meta">
                            <span class="resource-tag">{{ $resource['category'] }}</span>
                            <span>{{ $resource['size'] }}</span>
                            <span>{{ $resource['date'] }}</span>
                        </div>
                    </div>
                </div>

                <a class="action-button download-button" href="{{ ! empty($resource['path']) ? asset('storage/'.$resource['path']) : '#' }}" @if (! empty($resource['path'])) download @endif>
                    <span class="icon-box">{!! $icons['download'] !!}</span>
                    <span>Download</span>
                </a>
            </article>
        @endforeach
    </section>

    <div class="resources-empty-state" data-resources-empty>
        No resources match your current search or filters.
    </div>

    <div class="resources-upload-modal @if ($errors->any()) is-open @endif" data-resource-upload-modal>
        <button class="resources-upload-backdrop" type="button" aria-label="Close upload resource form" data-resource-upload-close></button>
        <div class="resources-upload-panel">
            <div class="resources-upload-header">
                <div>
                    <h3 class="resources-upload-title">Upload resource</h3>
                    <p class="resources-upload-copy">Add a file to one of your joined groups.</p>
                </div>
                <button class="resources-upload-close" type="button" aria-label="Close upload resource form" data-resource-upload-close>&times;</button>
            </div>

            <div class="resources-upload-body">
                @if ($errors->any())
                    <div class="resources-errors">
                        Please fix the following:
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="resources-upload-form" method="POST" action="{{ route('studyhub.student.resources.store') }}" enctype="multipart/form-data">
                    @csrf
                    <label class="resources-upload-field">
                        <span class="resources-upload-label">Study group</span>
                        <select class="resources-upload-select" name="group_id" required>
                            <option value="">Choose group</option>
                            @foreach ($uploadGroups as $group)
                                <option value="{{ $group['id'] }}" @selected((string) old('group_id') === (string) $group['id'])>{{ $group['name'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="resources-upload-field">
                        <span class="resources-upload-label">Category</span>
                        <select class="resources-upload-select" name="category" required>
                            <option value="">Choose category</option>
                            @foreach (array_slice($categories, 1) as $category)
                                <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="resources-upload-field">
                        <span class="resources-upload-label">File</span>
                        <input class="resources-upload-file" type="file" name="resource_file" required>
                    </label>

                    <div class="resources-upload-actions">
                        <button class="resources-upload-submit" type="submit">Upload file</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.querySelector('[data-resource-upload-modal]');
            const openButton = document.querySelector('[data-resource-upload-open]');
            const closeButtons = document.querySelectorAll('[data-resource-upload-close]');
            const filterToggle = document.querySelector('[data-resource-filter-toggle]');
            const filterPanel = document.querySelector('[data-resource-filter-panel]');
            const searchInput = document.querySelector('[data-resource-search]');
            const groupSelect = document.querySelector('[data-resource-group]');
            const downloadableSelect = document.querySelector('[data-resource-downloadable]');
            const categoryButtons = Array.from(document.querySelectorAll('[data-resource-category]'));
            const resourceCards = Array.from(document.querySelectorAll('[data-resource-card]'));
            const emptyState = document.querySelector('[data-resources-empty]');
            let activeCategory = 'all';

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

            filterToggle?.addEventListener('click', function () {
                filterPanel?.classList.toggle('is-open');
            });

            const applyResourceFilters = function () {
                const searchTerm = (searchInput?.value || '').trim().toLowerCase();
                const selectedGroup = (groupSelect?.value || '').trim().toLowerCase();
                const selectedDownloadable = (downloadableSelect?.value || '').trim().toLowerCase();
                let visibleCount = 0;

                resourceCards.forEach(function (card) {
                    const matchesSearch = searchTerm === ''
                        || card.dataset.name.includes(searchTerm)
                        || card.dataset.group.includes(searchTerm)
                        || card.dataset.category.includes(searchTerm)
                        || card.dataset.author.includes(searchTerm);
                    const matchesCategory = activeCategory === 'all' || card.dataset.category === activeCategory;
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
            groupSelect?.addEventListener('change', applyResourceFilters);
            downloadableSelect?.addEventListener('change', applyResourceFilters);

            categoryButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    categoryButtons.forEach(function (item) {
                        item.classList.remove('active');
                    });

                    button.classList.add('active');
                    activeCategory = button.dataset.resourceCategory || 'all';
                    applyResourceFilters();
                });
            });

            applyResourceFilters();

            if (modal.classList.contains('is-open')) {
                document.body.classList.add('overflow-hidden');
            }
        });
    </script>
@endsection
