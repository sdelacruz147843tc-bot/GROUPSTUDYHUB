@extends('studyhub.student.layout')

@section('title', 'Study Groups')

@php
    $createGroupErrors = $errors->createGroup;
    $joinGroupErrors = $errors->joinGroup;
    $failedJoinGroupId = (int) old('join_group_id');
    $failedJoinGroup = collect($groups)->firstWhere('id', $failedJoinGroupId);
    $groupCategoryChips = ['All Groups', 'Mathematics', 'Programming', 'Science', 'Language', 'Business', 'Design', 'Other'];
    $groupCategoryAliases = [
        'mathematics' => ['mathematics', 'math', 'calculus', 'algebra', 'geometry', 'statistics', 'trigonometry'],
        'programming' => ['programming', 'computer science', 'coding', 'code', 'web', 'software', 'database', 'artificial intelligence', 'ai'],
        'science' => ['science', 'biology', 'chemistry', 'physics', 'research'],
        'language' => ['language', 'english', 'filipino', 'writing', 'literature'],
        'business' => ['business', 'finance', 'accounting', 'marketing', 'information system', 'information systems'],
        'design' => ['design', 'art', 'ui', 'ux', 'graphics'],
        'other' => ['other', 'general'],
    ];
    $groupImages = [
        'artificial intelligence' => 'Artificial intelegence.png',
        'business' => 'business.png',
        'calculus' => 'Mathematics.png',
        'computer science' => 'Computer Science.png',
        'database' => 'Information system.png',
        'design' => 'design.png',
        'english' => 'General.png',
        'general' => 'General.png',
        'information system' => 'Information system.png',
        'information systems' => 'Information system.png',
        'language' => 'language.png',
        'math' => 'Mathematics.png',
        'mathematics' => 'Mathematics.png',
        'other' => 'General.png',
        'programming' => 'Programming.png',
        'research' => 'General.png',
        'science' => 'science.png',
        'web' => 'Programming.png',
    ];
    $groupThemes = [
        'artificial intelligence' => ['rgb' => '40, 28, 88', 'accent' => '139, 92, 246', 'accentHex' => '#a78bfa'],
        'business' => ['rgb' => '37, 18, 82', 'accent' => '139, 92, 246', 'accentHex' => '#a78bfa'],
        'calculus' => ['rgb' => '12, 45, 75', 'accent' => '56, 189, 248', 'accentHex' => '#7dd3fc'],
        'computer science' => ['rgb' => '12, 45, 75', 'accent' => '56, 189, 248', 'accentHex' => '#7dd3fc'],
        'database' => ['rgb' => '60, 39, 13', 'accent' => '245, 158, 11', 'accentHex' => '#fbbf24'],
        'design' => ['rgb' => '92, 43, 4', 'accent' => '245, 158, 11', 'accentHex' => '#fbbf24'],
        'english' => ['rgb' => '16, 54, 37', 'accent' => '74, 222, 128', 'accentHex' => '#86efac'],
        'general' => ['rgb' => '16, 54, 37', 'accent' => '74, 222, 128', 'accentHex' => '#86efac'],
        'information system' => ['rgb' => '60, 39, 13', 'accent' => '245, 158, 11', 'accentHex' => '#fbbf24'],
        'information systems' => ['rgb' => '60, 39, 13', 'accent' => '245, 158, 11', 'accentHex' => '#fbbf24'],
        'language' => ['rgb' => '8, 46, 95', 'accent' => '56, 189, 248', 'accentHex' => '#7dd3fc'],
        'math' => ['rgb' => '12, 45, 75', 'accent' => '56, 189, 248', 'accentHex' => '#7dd3fc'],
        'mathematics' => ['rgb' => '12, 45, 75', 'accent' => '56, 189, 248', 'accentHex' => '#7dd3fc'],
        'other' => ['rgb' => '16, 54, 37', 'accent' => '74, 222, 128', 'accentHex' => '#86efac'],
        'programming' => ['rgb' => '40, 28, 88', 'accent' => '139, 92, 246', 'accentHex' => '#a78bfa'],
        'research' => ['rgb' => '12, 45, 75', 'accent' => '56, 189, 248', 'accentHex' => '#7dd3fc'],
        'science' => ['rgb' => '6, 52, 27', 'accent' => '34, 197, 94', 'accentHex' => '#86efac'],
        'web' => ['rgb' => '40, 28, 88', 'accent' => '139, 92, 246', 'accentHex' => '#a78bfa'],
    ];
    $groupThemeFor = function (array $group) use ($groupImages, $groupThemes): array {
        $haystack = strtolower(($group['category'] ?? '').' '.($group['name'] ?? '').' '.($group['description'] ?? ''));

        foreach ($groupImages as $keyword => $image) {
            if (str_contains($haystack, $keyword)) {
                return [
                    'image' => asset('images/'.$image),
                    'theme' => $groupThemes[$keyword] ?? $groupThemes['general'],
                ];
            }
        }

        return [
            'image' => asset('images/General.png'),
            'theme' => $groupThemes['general'],
        ];
    };
    $groupFilterCategoryFor = function (array $group) use ($groupCategoryAliases): string {
        $haystack = strtolower(($group['category'] ?? '').' '.($group['name'] ?? '').' '.($group['description'] ?? ''));

        foreach ($groupCategoryAliases as $category => $aliases) {
            foreach ($aliases as $alias) {
                if (str_contains($haystack, $alias)) {
                    return $category;
                }
            }
        }

        return 'other';
    };
@endphp

@section('page')
    <div class="groups-toolbar">
        <div>
            <h2 class="page-title">Study Groups</h2>
            <p class="page-subtitle">Join and collaborate with your peers</p>
        </div>
        <div class="groups-controls">
            <div class="search-box">
                <span class="icon-box">{!! $icons['search'] !!}</span>
                <input type="text" placeholder="Search groups by name, subject, or keyword..." data-group-search>
            </div>
            <select class="groups-filter-select" data-group-category>
                <option value="">All Categories</option>
                @foreach (collect($groupCategories)->reject(fn ($category) => $category === 'General')->values() as $category)
                    <option value="{{ strtolower($category) }}">{{ $category }}</option>
                @endforeach
            </select>
            <select class="groups-filter-select" data-group-visibility>
                <option value="">All Access</option>
                <option value="public">Public</option>
                <option value="private">Private</option>
                <option value="joined">Joined</option>
            </select>
            <button class="action-button groups-action" type="button" data-create-group-open>
                <span class="icon-box">{!! $icons['plus'] !!}</span>
                <span>Create Group</span>
            </button>
        </div>
    </div>

    <div class="groups-category-strip" aria-label="Group categories">
        @foreach ($groupCategoryChips as $chip)
            @php $chipValue = $chip === 'All Groups' ? '' : strtolower($chip); @endphp
            <button
                class="group-category-chip {{ $loop->first ? 'is-active' : '' }}"
                type="button"
                data-group-category-chip="{{ $chipValue }}"
            >
                {{ $chip }}
            </button>
        @endforeach
    </div>

    <section class="groups-feature-strip">
        <button class="groups-feature-dismiss" type="button" aria-label="Dismiss">&times;</button>
        <div class="groups-feature-art" style="--groups-feature-image: url('{{ asset('images/up.png') }}');"></div>
        <div class="groups-feature-copy">
            <h3>Study better together!</h3>
            <p>Collaborate, share resources, and achieve more as a team.</p>
        </div>
        <div class="groups-feature-item">
            <span class="icon-box">{!! $icons['users'] !!}</span>
            <strong>Collaborate</strong>
            <span>Work together seamlessly</span>
        </div>
        <div class="groups-feature-item">
            <span class="icon-box">{!! $icons['file'] ?? $icons['resources'] ?? $icons['book'] !!}</span>
            <strong>Share Resources</strong>
            <span>Access quality materials</span>
        </div>
        <div class="groups-feature-item">
            <span class="icon-box">{!! $icons['chart'] ?? $icons['sparkles'] ?? $icons['users'] !!}</span>
            <strong>Achieve Goals</strong>
            <span>Learn and grow together</span>
        </div>
    </section>

    <section class="groups-grid">
        @foreach ($groups as $group)
            @php
                $isJoined = in_array((int) $group['id'], $joinedGroupIds ?? [], true);
                $meetingStyle = ucfirst(str_replace('-', ' ', $group['meeting_style'] ?? 'in-person'));
                $groupTheme = $groupThemeFor($group);
                $filterCategory = $groupFilterCategoryFor($group);
            @endphp
            <article
                class="content-card group-card"
                style="--group-bg-image: url('{{ $groupTheme['image'] }}'); --group-card-rgb: {{ $groupTheme['theme']['rgb'] }}; --group-card-accent-rgb: {{ $groupTheme['theme']['accent'] }}; --group-card-accent: {{ $groupTheme['theme']['accentHex'] }};"
                data-group-card
                data-name="{{ strtolower($group['name']) }}"
                data-description="{{ strtolower($group['description']) }}"
                data-category="{{ strtolower($group['category'] ?? 'general') }}"
                data-filter-category="{{ $filterCategory }}"
                data-visibility="{{ strtolower($group['visibility'] ?? 'public') }}"
                data-joined="{{ $isJoined ? 'yes' : 'no' }}"
            >
                <a class="group-card-link" href="{{ route('studyhub.student.group.show', $group['id']) }}">
                    <div class="group-card-cover">
                        <span class="icon-box group-card-icon">{!! $icons['users'] !!}</span>
                    </div>
                    <div class="group-card-body">
                        <div class="group-card-content">
                            <span class="group-card-access">{{ $meetingStyle }}</span>
                            <h3 class="group-card-title">{{ $group['name'] }}</h3>
                            <p class="group-card-copy">{{ $group['description'] }}</p>
                            <div class="group-card-tags">
                                <span class="group-card-tag">{{ $group['category'] ?? 'General' }}</span>
                                <span class="group-card-tag">{{ $meetingStyle }}</span>
                                @if (($group['visibility'] ?? 'public') === 'private')
                                    <span class="group-card-tag private">
                                        <span class="icon-box">{!! $icons['lock'] !!}</span>
                                        <span>Private</span>
                                    </span>
                                @endif
                            </div>
                            <div class="group-card-meta">
                                <span class="group-card-meta-item">
                                    <span class="icon-box">{!! $icons['users'] !!}</span>
                                    <span>{{ $group['members'] }} members</span>
                                </span>
                                <span class="group-card-meta-item">
                                    <span>{{ $group['resources'] }} Resources</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
                <div class="group-card-actions">
                    <a class="group-open-link" href="{{ route('studyhub.student.group.show', $group['id']) }}">Open</a>
                    @if ($isJoined)
                        <span class="group-join-button joined">Joined ✓</span>
                    @elseif (($group['visibility'] ?? 'public') === 'private')
                        <button
                            class="group-join-button private"
                            type="button"
                            data-private-join-open
                            data-join-action="{{ route('studyhub.student.groups.join', $group['id']) }}"
                            data-join-group-id="{{ $group['id'] }}"
                            data-join-group-name="{{ $group['name'] }}"
                        >Join</button>
                    @else
                        <form class="group-join-form" method="POST" action="{{ route('studyhub.student.groups.join', $group['id']) }}">
                            @csrf
                            <button class="group-join-button" type="submit" data-loading-label="Joining...">Join</button>
                        </form>
                    @endif
                </div>
            </article>
        @endforeach
    </section>

    <div class="groups-empty-state app-empty-state hidden" data-groups-empty>
        <span class="app-empty-icon">{!! $icons['users'] !!}</span>
        <strong data-empty-title>No groups found</strong>
        <span data-empty-copy>Try a different search or start a new study group.</span>
    </div>

    <x-studyhub.modal
        title="Join private group"
        :subtitle="$failedJoinGroup ? $failedJoinGroup['name'] : 'Enter the group code'"
        close-data="data-private-join-close"
        :open="$joinGroupErrors->any()"
        size="sm"
        data-private-join-modal
    >
        @if ($joinGroupErrors->any())
            <div class="groups-errors" role="alert" aria-live="polite">
                {{ $joinGroupErrors->first('join_code') }}
            </div>
        @endif

        <form class="grid gap-4" method="POST" action="{{ $failedJoinGroup ? route('studyhub.student.groups.join', $failedJoinGroup['id']) : '#' }}" data-private-join-form>
            @csrf
            <input type="hidden" name="join_group_id" value="{{ $failedJoinGroupId ?: '' }}" data-private-join-group-id>

            <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                <span class="text-sm font-extrabold text-[#244231]">Join code</span>
                <input
                    class="h-[54px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100"
                    type="text"
                    name="join_code"
                    maxlength="24"
                    placeholder="Enter the private group code"
                    value="{{ old('join_code') }}"
                    data-private-join-code
                    required
                >
            </label>

            <div class="flex justify-end gap-3">
                <button class="secondary-button" type="button" data-private-join-close>Cancel</button>
                <button class="group-join-button" type="submit" data-loading-label="Joining...">Join Group</button>
            </div>
        </form>
    </x-studyhub.modal>

    <x-studyhub.modal
        title="Start a new study group"
        subtitle="Set up a space for classmates to share resources, plan sessions, and keep discussions in one place."
        close-data="data-create-group-close"
        :open="$createGroupErrors->any()"
        size="xl"
        data-create-group-modal
    >
        <x-slot:kicker>
            <span class="inline-flex min-h-8 items-center rounded-full bg-white/75 px-4 text-xs font-black uppercase tracking-[0.08em] text-[#244231]">New Workspace</span>
        </x-slot:kicker>
                @if ($createGroupErrors->any())
                    <div class="groups-errors" role="alert" aria-live="polite">
                        <strong>Group was not created</strong>
                        <ul>
                            @foreach ($createGroupErrors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="grid grid-cols-1 gap-3 md:grid-cols-2" method="POST" action="{{ route('studyhub.student.groups.store') }}">
                    @csrf
                    <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                        <span class="text-sm font-extrabold text-[#244231]">Group name</span>
                        <input class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" type="text" name="name" maxlength="80" placeholder="Operating Systems Review" value="{{ old('name') }}" required>
                    </label>

                    <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                        <span class="text-sm font-extrabold text-[#244231]">Category</span>
                        <select class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" name="category">
                            @foreach ($groupCategories as $category)
                                <option value="{{ $category }}" @selected(old('category', 'General') === $category)>{{ $category }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4 md:col-span-2">
                        <span class="text-sm font-extrabold text-[#244231]">Description</span>
                        <textarea class="min-h-[120px] w-full resize-y rounded-2xl border border-emerald-100 bg-white/95 p-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" name="description" maxlength="160" placeholder="Share notes and plan review sessions." required>{{ old('description') }}</textarea>
                    </label>

                    <div class="rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4 md:col-span-2">
                        <span class="mb-3 block text-sm font-extrabold text-[#244231]">Meeting style</span>
                        <div class="groups-style-grid">
                            <label class="groups-style-option">
                                <input type="radio" name="meeting_style" value="in-person" @checked(old('meeting_style', 'in-person') === 'in-person')>
                                <span class="groups-style-card">
                                    <span class="groups-style-icon in-person">IRL</span>
                                    <span class="groups-style-copy">
                                        <strong>In person</strong>
                                        <span>Campus meetups</span>
                                    </span>
                                </span>
                            </label>

                            <label class="groups-style-option">
                                <input type="radio" name="meeting_style" value="online" @checked(old('meeting_style') === 'online')>
                                <span class="groups-style-card">
                                    <span class="groups-style-icon online">WEB</span>
                                    <span class="groups-style-copy">
                                        <strong>Online</strong>
                                        <span>Remote setup</span>
                                    </span>
                                </span>
                            </label>

                            <label class="groups-style-option">
                                <input type="radio" name="meeting_style" value="hybrid" @checked(old('meeting_style') === 'hybrid')>
                                <span class="groups-style-card">
                                    <span class="groups-style-icon hybrid">MIX</span>
                                    <span class="groups-style-copy">
                                        <strong>Hybrid</strong>
                                        <span>Both ways</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4 md:col-span-2">
                        <span class="mb-3 block text-sm font-extrabold text-[#244231]">Visibility</span>
                        <div class="groups-visibility-grid">
                            <label class="groups-style-option">
                                <input type="radio" name="visibility" value="public" @checked(old('visibility', 'public') === 'public')>
                                <span class="groups-style-card">
                                    <span class="groups-style-icon in-person">PUB</span>
                                    <span class="groups-style-copy">
                                        <strong>Public</strong>
                                        <span>Anyone can join</span>
                                    </span>
                                </span>
                            </label>

                            <label class="groups-style-option">
                                <input type="radio" name="visibility" value="private" @checked(old('visibility') === 'private')>
                                <span class="groups-style-card">
                                    <span class="groups-style-icon online">{!! $icons['lock'] !!}</span>
                                    <span class="groups-style-copy">
                                        <strong>Private</strong>
                                        <span>Join code needed</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <label class="groups-join-code-field flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4 md:col-span-2" data-join-code-field>
                        <span class="text-sm font-extrabold text-[#244231]">Join code</span>
                        <input class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" type="text" name="join_code" maxlength="24" placeholder="Only for private groups" value="{{ old('join_code') }}">
                    </label>

                    <div class="sticky bottom-0 -mx-5 mt-1 flex justify-end border-t border-emerald-100 bg-white/90 px-5 py-4 backdrop-blur sm:-mx-6 sm:px-6 md:col-span-2">
                        <button class="min-h-[54px] w-full rounded-2xl bg-emerald-500 px-6 font-extrabold text-white shadow-[0_14px_28px_rgba(73,182,112,0.22)] transition hover:bg-emerald-600 sm:w-auto sm:min-w-[180px]" type="submit" data-loading-label="Creating group...">Create Group</button>
                    </div>
                </form>
    </x-studyhub.modal>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.querySelector('[data-create-group-modal]');
            const privateJoinModal = document.querySelector('[data-private-join-modal]');
            const privateJoinForm = document.querySelector('[data-private-join-form]');
            const privateJoinGroupIdInput = document.querySelector('[data-private-join-group-id]');
            const privateJoinCodeInput = document.querySelector('[data-private-join-code]');
            const visibilityInputs = document.querySelectorAll('input[name="visibility"]');
            const joinCodeField = document.querySelector('[data-join-code-field]');

            if (modal) {
                window.StudyHubUI.bindModalTriggers({
                    modal: modal,
                    open: '[data-create-group-open]',
                    close: '[data-create-group-close]',
                });
            }

            if (privateJoinModal) {
                window.StudyHubUI.bindModalTriggers({
                    modal: privateJoinModal,
                    open: '[data-private-join-open]',
                    close: '[data-private-join-close]',
                    beforeOpen: function (button) {
                        privateJoinForm.action = button.dataset.joinAction;
                        privateJoinGroupIdInput.value = button.dataset.joinGroupId;
                        privateJoinModal.querySelector('p')?.replaceChildren(document.createTextNode(button.dataset.joinGroupName || 'Enter the group code'));
                        privateJoinCodeInput.value = '';
                    },
                    afterOpen: function () {
                        privateJoinCodeInput?.focus();
                    },
                });
            }

            const syncJoinCodeField = function () {
                const selectedVisibility = document.querySelector('input[name="visibility"]:checked')?.value || 'public';
                joinCodeField?.classList.toggle('is-visible', selectedVisibility === 'private');
            };

            visibilityInputs.forEach(function (input) {
                input.addEventListener('change', syncJoinCodeField);
            });
            syncJoinCodeField();

            const searchInput = document.querySelector('[data-group-search]');
            const categorySelect = document.querySelector('[data-group-category]');
            const visibilitySelect = document.querySelector('[data-group-visibility]');
            const categoryChips = Array.from(document.querySelectorAll('[data-group-category-chip]'));
            const cards = Array.from(document.querySelectorAll('[data-group-card]'));
            const emptyState = document.querySelector('[data-groups-empty]');
            const featureStrip = document.querySelector('.groups-feature-strip');
            const featureDismiss = document.querySelector('.groups-feature-dismiss');

            const syncCategoryChips = function () {
                const category = (categorySelect?.value || '').trim().toLowerCase();

                categoryChips.forEach(function (chip) {
                    chip.classList.toggle('is-active', (chip.dataset.groupCategoryChip || '') === category);
                    chip.setAttribute('aria-pressed', (chip.dataset.groupCategoryChip || '') === category ? 'true' : 'false');
                });
            };

            const applyGroupFilters = function () {
                const searchTerm = (searchInput?.value || '').trim().toLowerCase();
                const category = (categorySelect?.value || '').trim().toLowerCase();
                const visibility = (visibilitySelect?.value || '').trim().toLowerCase();
                let visibleCount = 0;

                cards.forEach(function (card) {
                    const matchesSearch = searchTerm === ''
                        || card.dataset.name.includes(searchTerm)
                        || card.dataset.description.includes(searchTerm)
                        || card.dataset.category.includes(searchTerm)
                        || card.dataset.filterCategory.includes(searchTerm);
                    const matchesCategory = category === ''
                        || card.dataset.filterCategory === category
                        || card.dataset.category === category;
                    const matchesVisibility = visibility === ''
                        || (visibility === 'joined' && card.dataset.joined === 'yes')
                        || (visibility !== 'joined' && card.dataset.visibility === visibility);

                    const isVisible = matchesSearch && matchesCategory && matchesVisibility;
                    card.classList.toggle('is-hidden', !isVisible);

                    if (isVisible) {
                        visibleCount += 1;
                    }
                });

                window.StudyHubUI.setEmptyState(emptyState, {
                    visibleCount: visibleCount,
                    totalCount: cards.length,
                    emptyTitle: 'No groups yet',
                    emptyCopy: 'Groups you create or join will appear here.',
                    filteredTitle: 'No groups match your filters',
                    filteredCopy: 'Try clearing the search, category, or access filter.',
                });
            };

            searchInput?.addEventListener('input', applyGroupFilters);
            categorySelect?.addEventListener('change', function () {
                syncCategoryChips();
                applyGroupFilters();
            });
            categoryChips.forEach(function (chip) {
                chip.setAttribute('aria-pressed', chip.classList.contains('is-active') ? 'true' : 'false');

                chip.addEventListener('click', function () {
                    if (categorySelect) {
                        categorySelect.value = chip.dataset.groupCategoryChip || '';
                    }

                    syncCategoryChips();
                    applyGroupFilters();
                });
            });
            visibilitySelect?.addEventListener('change', applyGroupFilters);
            featureDismiss?.addEventListener('click', function () {
                featureStrip?.classList.add('is-hidden');
            });
            syncCategoryChips();
            applyGroupFilters();

            if (privateJoinModal?.classList.contains('is-open')) {
                privateJoinCodeInput?.focus();
            }
            window.StudyHubUI.syncBodyOverflow();
        });
    </script>
@endsection
