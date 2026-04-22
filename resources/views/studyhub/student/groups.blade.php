@extends('studyhub.student.layout')

@section('title', 'Study Groups')

@php
    $groupCoverThemes = [
        'Computer Science 301' => "linear-gradient(135deg, rgba(15, 76, 117, 0.82), rgba(50, 130, 184, 0.58)), url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 240'%3E%3Crect width='600' height='240' fill='%230f4c75'/%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='.22' stroke-width='3'%3E%3Cpath d='M70 174h76l34-42h92l32-54h76'/%3E%3Ccircle cx='70' cy='174' r='13'/%3E%3Ccircle cx='180' cy='132' r='13'/%3E%3Ccircle cx='272' cy='132' r='13'/%3E%3Ccircle cx='304' cy='78' r='13'/%3E%3Ccircle cx='380' cy='78' r='13'/%3E%3C/g%3E%3Ctext x='404' y='170' fill='%23ffffff' fill-opacity='.18' font-size='84' font-family='Arial, sans-serif' font-weight='700'%3ECS%3C/text%3E%3C/svg%3E\")",
        'Data Structures Study' => "linear-gradient(135deg, rgba(31, 99, 72, 0.78), rgba(110, 193, 145, 0.56)), url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 240'%3E%3Crect width='600' height='240' fill='%23257a56'/%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='.18' stroke-width='3'%3E%3Crect x='92' y='64' width='80' height='34' rx='8'/%3E%3Crect x='260' y='44' width='80' height='34' rx='8'/%3E%3Crect x='430' y='64' width='80' height='34' rx='8'/%3E%3Crect x='174' y='144' width='80' height='34' rx='8'/%3E%3Crect x='348' y='144' width='80' height='34' rx='8'/%3E%3Cpath d='M172 81h88M300 78v40M214 144v-29M388 144v-29M340 81h90'/%3E%3C/g%3E%3Ctext x='34' y='202' fill='%23ffffff' fill-opacity='.14' font-size='58' font-family='Arial, sans-serif' font-weight='700'%3Etrees %26 graphs%3C/text%3E%3C/svg%3E\")",
        'Calculus II Prep' => "linear-gradient(135deg, rgba(121, 83, 30, 0.78), rgba(255, 193, 94, 0.55)), url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 240'%3E%3Crect width='600' height='240' fill='%23c78a2c'/%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='.18' stroke-width='3'%3E%3Cpath d='M32 166c48 0 48-92 96-92s48 92 96 92 48-92 96-92 48 92 96 92 48-92 96-92'/%3E%3Cpath d='M32 146c48 0 48-62 96-62s48 62 96 62 48-62 96-62 48 62 96 62 48-62 96-62'/%3E%3C/g%3E%3Ctext x='380' y='78' fill='%23ffffff' fill-opacity='.18' font-size='58' font-family='Georgia, serif'%E2%88%ABf(x)dx%3C/text%3E%3C/svg%3E\")",
        'Web Development' => "linear-gradient(135deg, rgba(94, 61, 138, 0.8), rgba(140, 118, 255, 0.56)), url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 240'%3E%3Crect width='600' height='240' fill='%236b4ca5'/%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='.18' stroke-width='3'%3E%3Cpath d='M96 74l-38 46 38 46'/%3E%3Cpath d='M178 56l-30 128'/%3E%3Cpath d='M246 74l38 46-38 46'/%3E%3Crect x='332' y='54' width='176' height='118' rx='16'/%3E%3Cpath d='M332 86h176M376 54v118'/%3E%3C/g%3E%3Ctext x='356' y='144' fill='%23ffffff' fill-opacity='.18' font-size='44' font-family='Arial, sans-serif'%3Cdiv%3E%3C/text%3E%3C/svg%3E\")",
        'Database Systems' => "linear-gradient(135deg, rgba(20, 88, 104, 0.82), rgba(77, 186, 208, 0.56)), url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 240'%3E%3Crect width='600' height='240' fill='%231a7086'/%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='.18' stroke-width='3'%3E%3Cellipse cx='172' cy='72' rx='82' ry='24'/%3E%3Cpath d='M90 72v76c0 14 37 24 82 24s82-10 82-24V72'/%3E%3Cellipse cx='172' cy='148' rx='82' ry='24'/%3E%3Cellipse cx='402' cy='96' rx='82' ry='24'/%3E%3Cpath d='M320 96v52c0 14 37 24 82 24s82-10 82-24V96'/%3E%3Cellipse cx='402' cy='148' rx='82' ry='24'/%3E%3C/g%3E%3C/svg%3E\")",
        'Machine Learning Basics' => "linear-gradient(135deg, rgba(151, 62, 93, 0.8), rgba(241, 138, 177, 0.54)), url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 600 240'%3E%3Crect width='600' height='240' fill='%23b64f75'/%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='.2' stroke-width='3'%3E%3Ccircle cx='144' cy='76' r='14'/%3E%3Ccircle cx='236' cy='56' r='14'/%3E%3Ccircle cx='220' cy='150' r='14'/%3E%3Ccircle cx='324' cy='106' r='14'/%3E%3Ccircle cx='414' cy='66' r='14'/%3E%3Ccircle cx='448' cy='156' r='14'/%3E%3Cpath d='M158 76h64M236 70v66M250 60l60 36M232 144l78-28M338 100l62-28M334 112l100 38'/%3E%3C/g%3E%3Ctext x='40' y='204' fill='%23ffffff' fill-opacity='.16' font-size='52' font-family='Arial, sans-serif' font-weight='700'%3ENN%3C/text%3E%3C/svg%3E\")",
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
                    <div class="group-card-cover" style="background-image: {{ $groupCoverThemes[$group['name']] ?? 'linear-gradient(180deg, #9be3ae 0%, #8fdda5 100%)' }};">
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
                    emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
                }
            };

            searchInput?.addEventListener('input', applyGroupFilters);
            categorySelect?.addEventListener('change', applyGroupFilters);
            visibilitySelect?.addEventListener('change', applyGroupFilters);
            applyGroupFilters();

            if (modal.classList.contains('is-open')) {
                document.body.style.overflow = 'hidden';
            }
        });
    </script>
@endsection

