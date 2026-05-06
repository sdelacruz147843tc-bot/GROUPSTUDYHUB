@extends('studyhub.student.layout')

@section('title', 'Discussions')

@php
    $discussionStatIconClasses = [
        1 => 'bg-[linear-gradient(135deg,var(--student-accent-soft)_0%,var(--student-accent)_100%)] text-white',
        2 => 'bg-[linear-gradient(135deg,color-mix(in_srgb,var(--student-accent-soft)_44%,white_56%)_0%,color-mix(in_srgb,var(--student-accent)_68%,white_32%)_100%)] text-white',
        3 => 'bg-[linear-gradient(135deg,color-mix(in_srgb,var(--student-accent-pale)_24%,white_76%)_0%,color-mix(in_srgb,var(--student-accent-soft)_82%,white_18%)_100%)] text-white',
    ];
@endphp

@section('page')
    <div class="discussion-header">
        <div>
            <h2 class="page-title">Discussions</h2>
            <p class="page-subtitle">Engage with your study groups</p>
        </div>
        <div class="toolbar-actions">
            <button class="action-button discussion-cta" type="button" data-discussion-open>
                <span class="icon-box">{!! $icons['plus'] !!}</span>
                <span>Create Discussion</span>
            </button>
        </div>
    </div>

    <section class="discussion-stats-grid">
        @foreach ($stats as $stat)
            <article class="discussion-stat-card">
                <div class="icon-box discussion-stat-icon {{ $discussionStatIconClasses[$loop->iteration] ?? $discussionStatIconClasses[3] }}">
                    {!! $icons[$stat['icon']] !!}
                </div>
                <div>
                    <div class="discussion-stat-label">{{ $stat['label'] }}</div>
                    <div class="discussion-stat-value">{{ $stat['value'] }}</div>
                    <div class="discussion-stat-hint">Active in your groups</div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="content-card discussion-toolbar">
        <div class="search-box discussion-search">
            <span class="icon-box">{!! $icons['search'] !!}</span>
            <input type="text" placeholder="Search discussions..." data-discussion-search>
        </div>
        <select class="discussion-filter-select" data-discussion-sort>
            <option value="latest">Latest</option>
            <option value="popular">Most Viewed</option>
            <option value="replies">Most Replies</option>
        </select>
        <select class="discussion-filter-select" data-discussion-group>
            <option value="">All Groups</option>
            @foreach (collect($discussions->items())->pluck('group')->filter()->unique()->sort()->values() as $groupName)
                <option value="{{ strtolower($groupName) }}">{{ $groupName }}</option>
            @endforeach
        </select>
    </section>

    <section class="discussion-list">
        @foreach ($discussions as $discussion)
            <article
                class="content-card discussion-row {{ ($discussion['author'] ?? '') === $studentProfile['display_name'] ? 'is-own' : '' }}"
                data-discussion-row
                data-title="{{ strtolower($discussion['title']) }}"
                data-body="{{ strtolower($discussion['body'] ?? '') }}"
                data-author="{{ strtolower($discussion['author']) }}"
                data-group="{{ strtolower($discussion['group']) }}"
                data-views="{{ (int) $discussion['views'] }}"
                data-replies="{{ (int) $discussion['replies'] }}"
            >
                <span class="discussion-row-accent" aria-hidden="true"></span>
                <div class="discussion-main">
                    <div class="discussion-author-row">
                        <span class="discussion-author-avatar">
                            @if (! empty($discussion['author_avatar_url']))
                                <img src="{{ $discussion['author_avatar_url'] }}" alt="{{ $discussion['author'] }}">
                            @else
                                {{ $discussion['author_initials'] ?? '??' }}
                            @endif
                        </span>
                        <span class="discussion-author-copy">
                            <span class="discussion-author-name">
                                {{ $discussion['author'] }}
                                @if (($discussion['author'] ?? '') === $studentProfile['display_name'])
                                    <span class="discussion-you-badge">You</span>
                                @endif
                            </span>
                            <span class="discussion-author-role">{{ $discussion['group'] }}</span>
                        </span>
                        <span class="discussion-group-pill">#{{ Str::of($discussion['group'])->replace(' ', '') }}</span>
                        @if (($discussion['author'] ?? '') === $studentProfile['display_name'])
                            <span class="discussion-owner-pill">Your post</span>
                        @endif
                    </div>
                    <h3>{{ $discussion['title'] }}</h3>
                    <p class="discussion-excerpt">{{ Str::limit($discussion['body'] ?? 'Open the thread to continue the conversation.', 115) }}</p>
                    <div class="discussion-stats">
                        <span>
                            <span class="icon-box">{!! $icons['discussion'] !!}</span>
                            <span>{{ $discussion['replies'] }} {{ Str::plural('Reply', (int) $discussion['replies']) }}</span>
                        </span>
                        <span>
                            <span class="icon-box">{!! $icons['eye'] !!}</span>
                            <span>{{ $discussion['views'] }} Views</span>
                        </span>
                        <span>
                            <span class="icon-box">{!! $icons['clock'] !!}</span>
                            <span>{{ $discussion['last_active'] }}</span>
                        </span>
                    </div>
                </div>
                <div class="discussion-side">
                    <span class="discussion-side-label">{{ (int) $discussion['replies'] > 0 ? 'Active thread' : 'New topic' }}</span>
                    <div class="discussion-participants" aria-hidden="true">
                        <span>{{ $discussion['author_initials'] ?? '??' }}</span>
                        <span>{{ substr($discussion['group'], 0, 1) }}</span>
                        <small>+{{ max((int) $discussion['replies'], 0) }}</small>
                    </div>
                    <a class="thread-button" href="{{ route('studyhub.student.discussions.show', $discussion['id']) }}">
                        <span>View Thread</span>
                        <span aria-hidden="true">-></span>
                    </a>
                    @if (($discussion['author'] ?? '') === $studentProfile['display_name'])
                        <form class="discussion-delete-form" method="POST" action="{{ route('studyhub.student.discussions.delete', $discussion['id']) }}">
                            @csrf
                            @method('DELETE')
                            <button class="discussion-delete-button" type="submit" data-loading-label="Deleting...">Delete</button>
                        </form>
                    @endif
                </div>
            </article>
        @endforeach
    </section>

    <div class="discussion-empty-state app-empty-state hidden" data-discussion-empty>
        <span class="app-empty-icon">{!! $icons['discussion'] !!}</span>
        <strong data-empty-title>No discussions found</strong>
        <span data-empty-copy>Try another search or start a new topic.</span>
        <button class="app-empty-action" type="button" data-discussion-open>Create discussion</button>
    </div>

    @if (method_exists($discussions, 'links'))
        <div class="mt-6">
            {{ $discussions->links() }}
        </div>
    @endif

    <x-studyhub.modal
        title="Create discussion"
        subtitle="Post a new topic to one of your joined groups."
        close-data="data-discussion-close"
        :open="$errors->any()"
        data-discussion-modal
    >
                @if ($errors->any())
                    <div class="discussion-errors" role="alert" aria-live="polite">
                        <strong>Discussion was not posted</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="grid gap-3" method="POST" action="{{ route('studyhub.student.discussions.store') }}">
                    @csrf
                    <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                        <span class="text-sm font-extrabold text-[#244231]">Topic title</span>
                        <input class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" type="text" name="title" maxlength="120" placeholder="Ask a question or start a topic" value="{{ old('title') }}" required>
                    </label>

                    <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                        <span class="text-sm font-extrabold text-[#244231]">Study group</span>
                        <select class="h-[50px] w-full rounded-2xl border border-emerald-100 bg-white/95 px-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" name="group_id" required>
                            <option value="">Choose group</option>
                            @foreach ($discussionGroups as $group)
                                <option value="{{ $group['id'] }}" @selected((string) old('group_id') === (string) $group['id'])>{{ $group['name'] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="flex flex-col gap-2 rounded-[18px] border border-emerald-100 bg-emerald-50/35 p-4">
                        <span class="text-sm font-extrabold text-[#244231]">Message</span>
                        <textarea class="min-h-[150px] w-full resize-y rounded-2xl border border-emerald-100 bg-white/95 p-4 text-[#1f3528] outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100" name="body" maxlength="500" placeholder="Share your question, notes, or discussion topic." required>{{ old('body') }}</textarea>
                    </label>

                    <div class="sticky bottom-0 -mx-5 mt-1 flex justify-end border-t border-emerald-100 bg-white/90 px-5 py-4 backdrop-blur sm:-mx-6 sm:px-6">
                        <button class="min-h-[54px] w-full rounded-2xl bg-emerald-500 px-6 font-extrabold text-white shadow-[0_14px_28px_rgba(73,182,112,0.22)] transition hover:bg-emerald-600 sm:w-auto sm:min-w-[180px]" type="submit" data-loading-label="Creating discussion...">Create Discussion</button>
                    </div>
                </form>
    </x-studyhub.modal>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.querySelector('[data-discussion-modal]');
            const searchInput = document.querySelector('[data-discussion-search]');
            const groupFilter = document.querySelector('[data-discussion-group]');
            const sortSelect = document.querySelector('[data-discussion-sort]');
            const discussionList = document.querySelector('.discussion-list');
            const discussionRows = Array.from(document.querySelectorAll('[data-discussion-row]'));
            const emptyState = document.querySelector('[data-discussion-empty]');

            if (modal) {
                window.StudyHubUI.bindModalTriggers({
                    modal: modal,
                    open: '[data-discussion-open]',
                    close: '[data-discussion-close]',
                });
            }

            const applyDiscussionControls = function () {
                const searchTerm = (searchInput?.value || '').trim().toLowerCase();
                const selectedGroup = (groupFilter?.value || '').trim().toLowerCase();
                const sortMode = sortSelect?.value || 'latest';
                let visibleCount = 0;

                discussionRows.forEach(function (row) {
                    const matchesSearch = searchTerm === ''
                        || row.dataset.title.includes(searchTerm)
                        || row.dataset.body.includes(searchTerm)
                        || row.dataset.author.includes(searchTerm)
                        || row.dataset.group.includes(searchTerm);
                    const matchesGroup = selectedGroup === '' || row.dataset.group === selectedGroup;
                    const isVisible = matchesSearch && matchesGroup;

                    row.classList.toggle('is-hidden', ! isVisible);

                    if (isVisible) {
                        visibleCount += 1;
                    }
                });

                const sortedRows = [...discussionRows].sort(function (a, b) {
                    if (sortMode === 'popular') {
                        return Number(b.dataset.views) - Number(a.dataset.views);
                    }

                    if (sortMode === 'replies') {
                        return Number(b.dataset.replies) - Number(a.dataset.replies);
                    }

                    return 0;
                });

                sortedRows.forEach(function (row) {
                    discussionList?.appendChild(row);
                });

                window.StudyHubUI.setEmptyState(emptyState, {
                    visibleCount: visibleCount,
                    totalCount: discussionRows.length,
                    emptyTitle: 'No discussions yet',
                    emptyCopy: 'Threads from your joined groups will appear here.',
                    filteredTitle: 'No discussions match your filters',
                    filteredCopy: 'Try another search, group, or sort option.',
                });
            };

            searchInput?.addEventListener('input', applyDiscussionControls);
            groupFilter?.addEventListener('change', applyDiscussionControls);
            sortSelect?.addEventListener('change', applyDiscussionControls);
            applyDiscussionControls();

            window.StudyHubUI.syncBodyOverflow();
        });
    </script>
@endsection
