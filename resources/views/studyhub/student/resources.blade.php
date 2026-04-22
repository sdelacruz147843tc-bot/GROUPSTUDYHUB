@extends('studyhub.student.layout')

@section('title', 'Resource Library')

@push('page-styles')
    <style>
        .resources-header {
            margin-bottom: 22px;
        }

        .resources-header .page-title {
            font-size: 2.9rem;
            margin-bottom: 6px;
            letter-spacing: -0.04em;
        }

        .resources-header .page-subtitle {
            margin: 0;
            max-width: 720px;
            font-size: 1.05rem;
        }

        .resources-toolbar {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .resources-filter-panel {
            display: none;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin: 0 0 18px;
            padding: 14px;
            border-radius: 18px;
            border: 1px solid rgba(193, 216, 201, 0.9);
            background: rgba(255,255,255,0.92);
            box-shadow: 0 14px 26px rgba(80, 111, 95, 0.08);
        }

        .resources-filter-panel.is-open {
            display: flex;
        }

        .resources-filter-select {
            min-width: 180px;
            height: 46px;
            border-radius: 14px;
            border: 1px solid rgba(193, 216, 201, 0.9);
            background: #fbfcfd;
            padding: 0 14px;
            font: inherit;
            color: #355242;
        }

        .resources-toolbar .search-box {
            max-width: 650px;
            flex: 1 1 480px;
        }

        .resources-toolbar .search-box input {
            height: 48px;
            border-radius: 16px;
            border: 1px solid rgba(193, 216, 201, 0.9);
            background: rgba(255,255,255,0.94);
            box-shadow: 0 14px 26px rgba(80, 111, 95, 0.08);
        }

        .filter-button,
        .upload-button {
            min-height: 48px;
            border-radius: 14px;
            padding: 0 18px;
            font-size: 0.92rem;
        }

        .upload-button {
            min-height: 50px;
            padding: 0 22px;
            border-radius: 16px;
            font-size: 0.98rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            box-shadow: 0 14px 28px color-mix(in srgb, var(--student-accent) 24%, transparent 76%);
        }

        .resources-status,
        .resources-errors {
            margin: 0 0 18px;
            padding: 13px 15px;
            border-radius: 16px;
            font-size: 0.94rem;
        }

        .resources-status {
            border: 1px solid color-mix(in srgb, var(--student-accent) 24%, white 76%);
            background: color-mix(in srgb, var(--student-accent-pale) 74%, white 26%);
            color: var(--student-accent-text);
        }

        .resources-errors {
            border: 1px solid rgba(219, 137, 120, 0.22);
            background: rgba(255, 244, 240, 0.92);
            color: #8a3f32;
        }

        .resources-errors ul {
            margin: 8px 0 0;
            padding-left: 18px;
        }

        .category-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 0 0 24px;
        }

        .category-pill {
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid rgba(198, 216, 205, 0.95);
            background: rgba(255,255,255,0.95);
            font-weight: 700;
            font-size: 0.9rem;
            color: #385042;
            box-shadow: 0 10px 18px rgba(94, 120, 103, 0.05);
        }

        .category-pill.active {
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            color: white;
            border-color: transparent;
            font-weight: 700;
        }

        .resource-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px 22px;
        }

        .resource-card {
            padding: 18px;
            border-radius: 22px;
            border: 1px solid rgba(195, 215, 203, 0.92);
            background: linear-gradient(180deg, rgba(255,255,255,0.97) 0%, rgba(248,252,249,0.99) 100%);
            box-shadow: 0 24px 42px rgba(66, 95, 76, 0.1);
            transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
        }

        .resource-card:hover {
            transform: translateY(-4px);
            border-color: rgba(141, 193, 158, 0.92);
            box-shadow: 0 30px 48px rgba(66, 95, 76, 0.14);
        }

        .resource-card.is-hidden {
            display: none;
        }

        .resources-empty-state {
            display: none;
            padding: 40px 24px;
            border-radius: 22px;
            border: 1px dashed rgba(171, 198, 180, 0.9);
            background: rgba(255,255,255,0.72);
            color: #597063;
            text-align: center;
            font-weight: 600;
            margin-top: 18px;
        }

        .resource-card-top {
            display: flex;
            gap: 14px;
            margin-bottom: 16px;
        }

        .resource-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-pale) 76%, white 24%) 0%, color-mix(in srgb, var(--student-accent-soft) 28%, white 72%) 100%);
            color: var(--student-accent-text);
            flex: 0 0 auto;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.78);
        }

        .resource-icon svg {
            width: 22px;
            height: 22px;
        }

        .resource-copy {
            min-width: 0;
        }

        .resource-copy h3 {
            margin: 0 0 6px;
            font-size: 1.18rem;
            line-height: 1.35;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #183425;
        }

        .resource-group {
            color: #46604f;
            font-size: 0.96rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .resource-author {
            color: #738279;
            font-size: 0.84rem;
            margin-bottom: 12px;
        }

        .resource-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            color: #718077;
            font-size: 0.82rem;
            margin-bottom: 14px;
        }

        .resource-tag {
            padding: 6px 10px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--student-accent-pale) 84%, white 16%);
            color: var(--student-accent-text);
            font-size: 0.72rem;
            font-weight: 700;
        }

        .download-button {
            width: 100%;
            min-height: 40px;
            border-radius: 12px;
            justify-content: center;
            gap: 8px;
            font-size: 0.86rem;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
        }

        .download-button svg {
            width: 14px;
            height: 14px;
        }

        .resources-upload-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 28px;
            z-index: 40;
        }

        .resources-upload-modal.is-open {
            display: flex;
        }

        .resources-upload-backdrop {
            position: absolute;
            inset: 0;
            border: 0;
            background: rgba(15, 22, 17, 0.48);
            backdrop-filter: blur(8px);
            cursor: pointer;
        }

        .resources-upload-panel {
            position: relative;
            z-index: 1;
            width: min(640px, 100%);
            max-height: calc(100vh - 44px);
            overflow: auto;
            border-radius: 28px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background:
                radial-gradient(circle at top right, color-mix(in srgb, var(--student-accent-pale) 78%, white 22%), transparent 34%),
                linear-gradient(180deg, color-mix(in srgb, var(--student-accent-pale) 40%, white 60%) 0%, rgba(255,255,255,0.98) 100%);
            box-shadow: 0 28px 56px color-mix(in srgb, var(--student-accent-text) 18%, transparent 82%);
        }

        .resources-upload-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 22px 24px 16px;
            border-bottom: 1px solid color-mix(in srgb, var(--student-accent) 14%, white 86%);
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 72%, white 28%) 0%, color-mix(in srgb, var(--student-accent-pale) 68%, white 32%) 100%);
        }

        .resources-upload-title {
            margin: 0 0 6px;
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #183425;
        }

        .resources-upload-copy {
            margin: 0;
            color: color-mix(in srgb, var(--student-accent-text) 72%, white 28%);
            font-size: 0.95rem;
        }

        .resources-upload-close {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background: color-mix(in srgb, var(--student-accent-pale) 54%, white 46%);
            color: var(--student-accent-text);
            font-size: 1.4rem;
            line-height: 1;
            cursor: pointer;
            flex-shrink: 0;
        }

        .resources-upload-body {
            padding: 20px 24px 24px;
        }

        .resources-upload-form {
            display: grid;
            gap: 14px;
        }

        .resources-upload-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 16px;
            border-radius: 22px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background: linear-gradient(180deg, color-mix(in srgb, var(--student-accent-pale) 46%, white 54%) 0%, color-mix(in srgb, var(--student-accent-pale) 20%, white 80%) 100%);
        }

        .resources-upload-label {
            font-size: 0.95rem;
            font-weight: 700;
            color: #244231;
        }

        .resources-upload-input,
        .resources-upload-select,
        .resources-upload-file {
            width: 100%;
            border-radius: 16px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 14%, white 86%);
            background: rgba(255,255,255,0.92);
            color: #1f3528;
            font: inherit;
        }

        .resources-upload-select {
            height: 54px;
            padding: 0 16px;
        }

        .resources-upload-file {
            padding: 14px 16px;
        }

        .resources-upload-actions {
            display: flex;
            justify-content: flex-end;
            padding-top: 4px;
        }

        .resources-upload-submit {
            min-height: 54px;
            min-width: 180px;
            padding: 0 24px;
            border-radius: 16px;
            border: none;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            color: white;
            font-size: 0.98rem;
            font-weight: 800;
            box-shadow: 0 14px 28px color-mix(in srgb, var(--student-accent) 24%, transparent 76%);
            cursor: pointer;
        }

        @media (max-width: 900px) {
            .resources-header .page-title {
                font-size: 2.3rem;
            }

            .resource-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

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
                document.body.style.overflow = isOpen ? 'hidden' : '';
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
                    emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
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
                document.body.style.overflow = 'hidden';
            }
        });
    </script>
@endsection
