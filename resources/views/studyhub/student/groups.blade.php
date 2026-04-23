@extends('studyhub.student.layout')

@section('title', 'Study Groups')

@php
    $groupCoverThemes = [
        'Computer Science 301' => 'bg-gradient-to-br from-[#0f4c75] to-[#3282b8]',
        'Data Structures Study' => 'bg-gradient-to-br from-[#1f6348] to-[#6ec191]',
        'Calculus II Prep' => 'bg-gradient-to-br from-[#79531e] to-[#ffc15e]',
        'Web Development' => 'bg-gradient-to-br from-[#5e3d8a] to-[#8c76ff]',
        'Database Systems' => 'bg-gradient-to-br from-[#145868] to-[#4dbad0]',
        'Machine Learning Basics' => 'bg-gradient-to-br from-[#973e5d] to-[#f18ab1]',
    ];
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
                <input type="text" placeholder="Search groups..." data-group-search>
            </div>
            <select class="groups-filter-select" data-group-category>
                <option value="">All Categories</option>
                @foreach (collect($groups)->pluck('category')->filter()->unique()->sort()->values() as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
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

    <section class="groups-grid">
        @foreach ($groups as $group)
            <article
                class="content-card group-card"
                data-group-card
                data-name="{{ strtolower($group['name']) }}"
                data-description="{{ strtolower($group['description']) }}"
                data-category="{{ strtolower($group['category'] ?? 'general') }}"
                data-visibility="{{ strtolower($group['visibility'] ?? 'public') }}"
                data-joined="{{ in_array((int) $group['id'], $joinedGroupIds ?? [], true) ? 'yes' : 'no' }}"
            >
                <a class="group-card-link" href="{{ route('studyhub.student.group.show', $group['id']) }}">
                    <div class="group-card-cover {{ $groupCoverThemes[$group['name']] ?? 'bg-gradient-to-b from-[#9be3ae] to-[#8fdda5]' }}">
                        <span class="icon-box group-card-icon">{!! $icons['users'] !!}</span>
                    </div>
                    <div class="group-card-body">
                        <div class="group-card-content">
                            <h3 class="group-card-title">{{ $group['name'] }}</h3>
                            <div class="group-card-tags">
                                <span class="group-card-tag">{{ $group['category'] ?? 'General' }}</span>
                                <span class="group-card-tag">{{ ucfirst(str_replace('-', ' ', $group['meeting_style'] ?? 'in-person')) }}</span>
                                @if (($group['visibility'] ?? 'public') === 'private')
                                    <span class="group-card-tag private">
                                        <span class="icon-box">{!! $icons['lock'] !!}</span>
                                        <span>Private</span>
                                    </span>
                                @endif
                            </div>
                            <p class="group-card-copy">{{ $group['description'] }}</p>
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
                    @if (in_array((int) $group['id'], $joinedGroupIds ?? [], true))
                        <span class="group-join-button joined">Joined</span>
                    @elseif (($group['visibility'] ?? 'public') === 'private')
                        <a class="group-join-button private" href="{{ route('studyhub.student.group.show', $group['id']) }}">Private</a>
                    @else
                        <form class="group-join-form" method="POST" action="{{ route('studyhub.student.groups.join', $group['id']) }}">
                            @csrf
                            <button class="group-join-button" type="submit">Join</button>
                        </form>
                    @endif
                </div>
            </article>
        @endforeach
    </section>

    <div class="groups-empty-state" data-groups-empty>
        No study groups match your current search or filters.
    </div>

    <div class="groups-create-modal @if ($errors->any()) is-open @endif" data-create-group-modal>
        <button class="groups-create-backdrop" type="button" aria-label="Close create group form" data-create-group-close></button>
        <div class="groups-create-panel">
            <div class="groups-create-header">
                <div class="groups-create-intro">
                    <span class="groups-create-kicker">New Workspace</span>
                    <h3 class="groups-create-title">Start a new study group</h3>
                    <p class="groups-create-copy">Set up a space for classmates to share resources, plan sessions, and keep discussions in one place.</p>
                </div>
                <button class="groups-create-close" type="button" aria-label="Close create group form" data-create-group-close>&times;</button>
            </div>

            <div class="groups-create-body">
                @if ($errors->any())
                    <div class="groups-errors">
                        Please fix the following before creating the group:
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="groups-form" method="POST" action="{{ route('studyhub.student.groups.store') }}">
                    @csrf
                    <label class="groups-field">
                        <span class="groups-label">Group name</span>
                        <input class="groups-input" type="text" name="name" maxlength="80" placeholder="Operating Systems Review" value="{{ old('name') }}" required>
                    </label>

                    <label class="groups-field">
                        <span class="groups-label">Category</span>
                        <input class="groups-input" type="text" name="category" maxlength="40" placeholder="Computer Science" value="{{ old('category') }}">
                    </label>

                    <label class="groups-field groups-field-full">
                        <span class="groups-label">Description</span>
                        <textarea class="groups-textarea" name="description" maxlength="160" placeholder="Share notes and plan review sessions." required>{{ old('description') }}</textarea>
                    </label>

                    <div class="groups-field groups-field-full">
                        <span class="groups-label">Meeting style</span>
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

                    <div class="groups-field groups-field-full">
                        <span class="groups-label">Visibility</span>
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

                    <label class="groups-field groups-field-full">
                        <span class="groups-label">Join code</span>
                        <input class="groups-input" type="text" name="join_code" maxlength="24" placeholder="Only for private groups" value="{{ old('join_code') }}">
                        <span class="groups-join-hint">Leave blank for public groups.</span>
                    </label>

                    <div class="groups-form-actions">
                        <button class="groups-submit" type="submit">Create group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.querySelector('[data-create-group-modal]');
            const openButton = document.querySelector('[data-create-group-open]');
            const closeButtons = document.querySelectorAll('[data-create-group-close]');

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

            const searchInput = document.querySelector('[data-group-search]');
            const categorySelect = document.querySelector('[data-group-category]');
            const visibilitySelect = document.querySelector('[data-group-visibility]');
            const cards = Array.from(document.querySelectorAll('[data-group-card]'));
            const emptyState = document.querySelector('[data-groups-empty]');

            const applyGroupFilters = function () {
                const searchTerm = (searchInput?.value || '').trim().toLowerCase();
                const category = (categorySelect?.value || '').trim().toLowerCase();
                const visibility = (visibilitySelect?.value || '').trim().toLowerCase();
                let visibleCount = 0;

                cards.forEach(function (card) {
                    const matchesSearch = searchTerm === ''
                        || card.dataset.name.includes(searchTerm)
                        || card.dataset.description.includes(searchTerm)
                        || card.dataset.category.includes(searchTerm);
                    const matchesCategory = category === '' || card.dataset.category === category;
                    const matchesVisibility = visibility === ''
                        || (visibility === 'joined' && card.dataset.joined === 'yes')
                        || (visibility !== 'joined' && card.dataset.visibility === visibility);

                    const isVisible = matchesSearch && matchesCategory && matchesVisibility;
                    card.classList.toggle('is-hidden', !isVisible);

                    if (isVisible) {
                        visibleCount += 1;
                    }
                });

                if (emptyState) {
                    emptyState.classList.toggle('hidden', visibleCount !== 0);
                }
            };

            searchInput?.addEventListener('input', applyGroupFilters);
            categorySelect?.addEventListener('change', applyGroupFilters);
            visibilitySelect?.addEventListener('change', applyGroupFilters);
            applyGroupFilters();

            if (modal.classList.contains('is-open')) {
                document.body.classList.add('overflow-hidden');
            }
        });
    </script>
@endsection
