@extends('studyhub.student.layout')

@section('title', 'Discussions')

@php
    $discussionItems = collect(method_exists($discussions, 'items') ? $discussions->items() : $discussions);
    $groups = collect($discussionGroups);
    $joinedGroupIds = $groups->pluck('id')->map(fn ($id) => (string) $id)->all();
    $filters = $discussionFilters ?? ['tab' => 'all', 'q' => ''];
    $activeTab = in_array($filters['tab'] ?? 'all', ['all', 'latest', 'unanswered', 'helpful', 'mine'], true)
        ? ($filters['tab'] ?? 'all')
        : 'all';
    $discussionTabUrl = fn (string $tab): string => route('studyhub.student.discussions', array_filter([
        'tab' => $tab,
        'q' => $filters['q'] ?? '',
    ], fn ($value) => $value !== null && $value !== ''));
    $displayName = $studentProfile['display_name'] ?? '';
    $totalReplies = $discussionItems->sum(fn ($discussion) => (int) ($discussion['replies'] ?? 0));
    $withImages = $discussionItems->where('has_image', true)->count();
    $unanswered = $discussionItems->filter(fn ($discussion) => (int) ($discussion['replies'] ?? 0) === 0)->values();
    $trendingTags = $discussionItems
        ->groupBy('group')
        ->map(fn ($items, $group) => ['name' => $group, 'count' => $items->count()])
        ->sortByDesc('count')
        ->take(6)
        ->values();
    $activeAuthors = $discussionItems
        ->pluck('author_initials', 'author')
        ->filter()
        ->take(5);
@endphp

@push('styles')
    <style>
        .discussion-feed-page { color: #13241a; }
        .discussion-feed-header { display: flex; align-items: center; justify-content: space-between; gap: 18px; margin-bottom: 18px; }
        .discussion-feed-header .page-title { margin: 0; color: #111d33 !important; font-size: 1.9rem; font-weight: 950; line-height: 1; }
        .discussion-feed-header .page-subtitle { margin: 8px 0 0; color: #536277; font-size: .9rem; font-weight: 720; }
        .discussion-post-button { display: inline-flex; min-height: 42px; align-items: center; justify-content: center; gap: 8px; border: 0; border-radius: 8px; background: #09a64b; color: #fff; font-size: .84rem; font-weight: 950; cursor: pointer; box-shadow: 0 14px 24px rgba(9,166,75,.18); }
        .discussion-post-button { min-width: 82px; margin-left: auto; padding: 0 18px; }
        .discussion-feed-layout { display: grid; grid-template-columns: minmax(0, 1fr) 270px; gap: 16px; align-items: start; }
        .discussion-feed-main, .discussion-feed-sidebar, .discussion-feed-list { display: grid; gap: 14px; min-width: 0; }
        .discussion-composer-card, .discussion-filter-tabs, .discussion-feed-card, .discussion-side-card { border: 1px solid #dfeee5; border-radius: 10px; background: rgba(255,255,255,.94); box-shadow: 0 16px 36px rgba(43,74,53,.06); }
        .discussion-composer-card { padding: 18px; }
        .discussion-composer-card form { display: grid; gap: 14px; margin: 0; }
        .discussion-composer-top { display: grid; grid-template-columns: 46px minmax(0,1fr); gap: 14px; }
        .discussion-author-avatar { display: inline-flex; width: 46px; height: 46px; align-items: center; justify-content: center; border-radius: 999px; background: linear-gradient(135deg,#7ddaa2,#09a64b); color: #fff; font-size: .82rem; font-weight: 950; overflow: hidden; flex: 0 0 auto; }
        .discussion-author-avatar.small { width: 30px; height: 30px; font-size: .68rem; }
        .discussion-author-avatar img { width: 100%; height: 100%; display: block; object-fit: cover; }
        .discussion-composer-fields { display: grid; gap: 10px; }
        .discussion-composer-fields input, .discussion-composer-fields textarea { width: 100%; border: 1px solid #dcebe2; border-radius: 8px; background: #fff; color: #1f3528; font: inherit; font-weight: 760; outline: none; box-shadow: 0 10px 26px rgba(35,68,48,.04); }
        .discussion-composer-fields input { min-height: 46px; padding: 0 14px; }
        .discussion-composer-fields textarea { min-height: 82px; resize: vertical; padding: 13px 14px; }
        .discussion-composer-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding-left: 60px; }
        .discussion-tool-button, .discussion-group-select, .discussion-feed-search { display: inline-flex; min-height: 38px; align-items: center; gap: 8px; border: 1px solid #dcebe2; border-radius: 8px; background: #fff; color: #1f3528; padding: 0 12px; font-size: .78rem; font-weight: 950; cursor: pointer; }
        .discussion-tool-button input { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
        .discussion-group-select select, .discussion-feed-search input { border: 0; background: transparent; color: inherit; font: inherit; outline: none; }
        .discussion-image-preview-chip { display: none; align-items: center; gap: 8px; min-height: 42px; max-width: 280px; border: 1px solid #cfe8da; border-radius: 8px; background: #f8fffb; padding: 5px 8px 5px 5px; }
        .discussion-image-preview-chip.is-visible { display: inline-flex; }
        .discussion-image-preview-chip img { width: 32px; height: 32px; display: block; border-radius: 7px; object-fit: cover; background: #edf7f1; }
        .discussion-image-preview-chip span { display: grid; min-width: 0; color: #1f3528; font-size: .72rem; font-weight: 900; line-height: 1.1; }
        .discussion-image-preview-chip small { color: #65766d; font-size: .66rem; font-weight: 760; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .discussion-image-preview-chip button { display: inline-grid; width: 24px; height: 24px; place-content: center; border: 0; border-radius: 999px; background: #eaf6ef; color: #527061; font-size: 1rem; line-height: 1; cursor: pointer; }
        .discussion-filter-tabs { display: flex; align-items: center; gap: 8px; min-height: 58px; padding: 8px 12px; overflow-x: auto; }
        .discussion-filter-tabs a { display: inline-flex; min-height: 38px; align-items: center; gap: 8px; border: 1px solid transparent; border-radius: 8px; background: transparent; color: #56675d; padding: 0 10px; font-size: .78rem; font-weight: 900; white-space: nowrap; cursor: pointer; text-decoration: none; }
        .discussion-filter-tabs a svg, .discussion-feed-search svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2.4; stroke-linecap: round; stroke-linejoin: round; }
        .discussion-filter-tabs a.active { color: #079344; background: rgba(9,166,75,.1); border-color: rgba(9,166,75,.22); }
        .discussion-filter-tabs a:focus { outline: none; }
        .discussion-filter-tabs a:focus-visible { border-color: rgba(9,166,75,.42); box-shadow: 0 0 0 3px rgba(9,166,75,.14); }
        .discussion-feed-search { margin-left: auto; }
        .discussion-feed-search input { width: 92px; }
        .discussion-tool-button .icon-box, .discussion-group-select .icon-box, .discussion-feed-search .icon-box, .discussion-filter-tabs .icon-box { width: 16px; height: 16px; min-width: 16px; }
        .discussion-feed-card { position: relative; display: grid; grid-template-columns: 58px minmax(0,1fr) 26px; gap: 16px; align-items: center; padding: 18px; }
        .discussion-feed-card.has-image { grid-template-columns: 58px minmax(0,1fr) minmax(150px,240px) 26px; }
        .discussion-feed-card.is-hidden { display: none; }
        .discussion-feed-card[data-thread-url] { cursor: pointer; }
        .discussion-vote-rail { display: grid; gap: 4px; justify-items: center; align-self: stretch; color: #079344; font-size: .72rem; font-weight: 900; }
        .discussion-helpful-form { margin: 0; }
        .discussion-helpful-button { display: grid; gap: 2px; justify-items: center; border: 0; background: transparent; color: #079344; font: inherit; font-weight: 950; cursor: pointer; }
        .discussion-helpful-icon-box { display: inline-grid; width: 44px; height: 44px; place-content: center; border-radius: 8px; background: transparent; overflow: hidden; }
        .discussion-helpful-icon { width: 42px; height: 42px; display: block; object-fit: contain; transform: scale(1.55); }
        .discussion-helpful-button.is-active .discussion-helpful-icon { filter: drop-shadow(0 4px 8px rgba(9,166,75,.18)); }
        .discussion-vote-rail .icon-box { width: 20px; height: 20px; }
        .discussion-vote-rail strong { color: #09a64b; font-size: 1rem; }
        .discussion-vote-rail small { color: #7a8a81; font-size: .68rem; }
        .discussion-post-meta, .discussion-post-stats, .discussion-post-tags { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .discussion-post-meta { color: #65766d; font-size: .75rem; font-weight: 820; }
        .discussion-post-meta strong, .discussion-post-title, .discussion-side-card h3 { color: #12231a; }
        .discussion-group-pill, .discussion-type-pill, .discussion-you-badge { display: inline-flex; min-height: 22px; align-items: center; border-radius: 999px; padding: 0 9px; font-size: .68rem; font-weight: 950; }
        .discussion-group-pill { background: rgba(9,166,75,.12); color: #07853d; }
        .discussion-type-pill { background: rgba(134,94,255,.11); color: #6f45d6; }
        .discussion-you-badge { background: #eafaf0; color: #07853d; }
        .discussion-post-title { display: inline-block; margin-top: 9px; font-size: 1.05rem; font-weight: 950; line-height: 1.25; text-decoration: none; }
        .discussion-post-body p { margin: 6px 0 0; color: #43554b; font-size: .84rem; font-weight: 680; line-height: 1.55; }
        .discussion-post-tags { margin-top: 9px; }
        .discussion-post-tags span { color: #1d77d4; background: rgba(70,145,235,.1); border-radius: 999px; padding: 4px 8px; font-size: .68rem; font-weight: 900; }
        .discussion-post-stats { margin-top: 12px; color: #65766d; font-size: .76rem; font-weight: 820; }
        .discussion-post-stats span { display: inline-flex; align-items: center; gap: 6px; }
        .discussion-post-stats svg, .discussion-side-card h3 svg { width: 16px; height: 16px; }
        .discussion-image-preview { position: relative; display: block; width: 100%; aspect-ratio: 16 / 9; overflow: hidden; border-radius: 8px; border: 1px solid #dcebe2; background: #edf7f1; }
        .discussion-image-preview img { width: 100%; height: 100%; display: block; object-fit: cover; }
        .discussion-image-preview span { position: absolute; right: 8px; bottom: 8px; border-radius: 999px; background: rgba(17,29,51,.74); color: #fff; padding: 3px 8px; font-size: .68rem; font-weight: 900; }
        .discussion-card-menu { display: grid; gap: 8px; justify-items: end; align-self: start; }
        .discussion-card-menu a, .discussion-card-menu button { border: 0; background: transparent; color: #63766b; font-size: .78rem; font-weight: 950; text-decoration: none; cursor: pointer; }
        .discussion-card-menu button { color: #b54a3a; }
        .discussion-side-card { padding: 18px; }
        .discussion-side-card h3 { display: flex; align-items: center; gap: 8px; margin: 0 0 14px; font-size: .9rem; font-weight: 950; }
        .discussion-tag-list, .discussion-question-list, .discussion-how-list { display: grid; gap: 10px; }
        .discussion-tag-list span { display: flex; min-height: 28px; align-items: center; justify-content: space-between; gap: 8px; color: #244231; font-size: .76rem; font-weight: 900; }
        .discussion-tag-list strong { display: inline-flex; min-width: 28px; justify-content: center; border-radius: 999px; background: #eef7f1; color: #53645a; padding: 4px 8px; }
        .discussion-question-list a { display: grid; gap: 3px; color: #12231a; text-decoration: none; }
        .discussion-question-list strong { font-size: .78rem; font-weight: 950; line-height: 1.35; }
        .discussion-question-list span, .discussion-side-card p, .discussion-how-list li { color: #63766b; font-size: .74rem; font-weight: 760; }
        .discussion-live-dot { width: 8px; height: 8px; border-radius: 999px; background: #09a64b; }
        .discussion-active-row { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .discussion-active-row span, .discussion-active-row small { display: inline-flex; width: 34px; height: 34px; align-items: center; justify-content: center; border-radius: 999px; background: linear-gradient(135deg,#dff8e8,#9be8b8); color: #0a7d3b; font-size: .7rem; font-weight: 950; }
        .discussion-active-row small { background: #f2f7f4; color: #63766b; }
        .discussion-how-list { margin: 0; padding: 0; list-style: none; }
        .discussion-how-list li { position: relative; padding-left: 18px; }
        .discussion-how-list li::before { content: ""; position: absolute; left: 0; top: .45em; width: 8px; height: 8px; border-radius: 999px; background: #09a64b; }
        @media (max-width: 1100px) { .discussion-feed-layout { grid-template-columns: 1fr; } .discussion-feed-sidebar { grid-template-columns: repeat(2,minmax(0,1fr)); } }
        @media (max-width: 760px) { .discussion-feed-header, .discussion-composer-actions { align-items: stretch; flex-direction: column; } .discussion-composer-actions { padding-left: 0; } .discussion-post-button { width: 100%; margin-left: 0; } .discussion-feed-card, .discussion-feed-card.has-image { grid-template-columns: 1fr; } .discussion-vote-rail { display: flex; align-items: center; justify-content: flex-start; } .discussion-card-menu { justify-items: start; } .discussion-feed-sidebar { grid-template-columns: 1fr; } }
    </style>
@endpush

@section('page')
    <div class="discussion-feed-page">
        <header class="discussion-feed-header">
            <div>
                <h2 class="page-title">Discussions</h2>
                <p class="page-subtitle">Ask questions, share ideas, and solve problems together.</p>
            </div>
        </header>

        <div class="discussion-feed-layout">
            <main class="discussion-feed-main">
                <section class="discussion-composer-card {{ $errors->any() || old('title') || old('body') ? 'is-expanded' : '' }}" data-discussion-composer>
                    @if ($errors->any())
                        <div class="discussion-errors" role="alert" aria-live="polite">
                            <strong>Post was not created</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('studyhub.student.discussions.store') }}" enctype="multipart/form-data" data-discussion-post-form>
                        @csrf
                        <div class="discussion-composer-top">
                            <span class="discussion-author-avatar">
                                @if (! empty($studentProfile['avatar_url']))
                                    <img src="{{ $studentProfile['avatar_url'] }}" alt="{{ $displayName }}">
                                @else
                                    {{ $studentProfile['initials'] ?? 'ME' }}
                                @endif
                            </span>
                            <div class="discussion-composer-fields">
                                <input type="text" name="title" maxlength="120" value="{{ old('title') }}" placeholder="Ask a question or share something with your group..." required>
                                <textarea name="body" maxlength="500" placeholder="Add details so classmates can help." required>{{ old('body') }}</textarea>
                            </div>
                        </div>

                        <div class="discussion-composer-actions">
                            <label class="discussion-tool-button">
                                <span class="icon-box">{!! $icons['file'] !!}</span>
                                <span>Images</span>
                                <input type="file" name="discussion_images[]" accept="image/png,image/jpeg,image/webp,image/gif" multiple data-discussion-image-input>
                            </label>
                            <label class="discussion-group-select">
                                <span class="icon-box">{!! $icons['users'] !!}</span>
                                <select name="group_id" required>
                                    <option value="">Choose Group</option>
                                    @foreach ($groups as $group)
                                        <option value="{{ $group['id'] }}" @selected((string) old('group_id') === (string) $group['id'])>{{ $group['name'] }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <div class="discussion-image-preview-chip" data-discussion-image-preview hidden>
                                <span class="discussion-image-preview-stack" data-discussion-image-preview-stack></span>
                                <span>
                                    <strong data-discussion-image-name></strong>
                                    <small data-discussion-image-size></small>
                                </span>
                                <button type="button" aria-label="Remove selected image" data-discussion-image-clear>&times;</button>
                            </div>
                            <button class="discussion-post-button" type="submit" data-loading-label="Posting...">Post</button>
                        </div>
                    </form>
                </section>

                <nav class="discussion-filter-tabs" aria-label="Discussion filters">
                    <a class="{{ $activeTab === 'all' ? 'active' : '' }}" href="{{ $discussionTabUrl('all') }}" data-discussion-tab="all"><span class="icon-box">{!! $icons['discussion'] !!}</span><span>All Posts</span></a>
                    <a class="{{ $activeTab === 'latest' ? 'active' : '' }}" href="{{ $discussionTabUrl('latest') }}" data-discussion-tab="latest"><span class="icon-box">{!! $icons['clock'] !!}</span><span>Latest</span></a>
                    <a class="{{ $activeTab === 'unanswered' ? 'active' : '' }}" href="{{ $discussionTabUrl('unanswered') }}" data-discussion-tab="unanswered"><span class="icon-box">{!! $icons['message'] !!}</span><span>Unanswered</span></a>
                    <a class="{{ $activeTab === 'helpful' ? 'active' : '' }}" href="{{ $discussionTabUrl('helpful') }}" data-discussion-tab="helpful"><span class="icon-box">{!! $icons['trend'] !!}</span><span>Most Helpful</span></a>
                    <a class="{{ $activeTab === 'mine' ? 'active' : '' }}" href="{{ $discussionTabUrl('mine') }}" data-discussion-tab="mine"><span class="icon-box">{!! $icons['users'] !!}</span><span>My Groups</span></a>
                    <form class="discussion-feed-search {{ filled($filters['q'] ?? '') ? 'is-active' : '' }}" method="GET" action="{{ route('studyhub.student.discussions') }}" role="search">
                        <input type="hidden" name="tab" value="{{ $activeTab }}">
                        <span class="icon-box">{!! $icons['search'] !!}</span>
                        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Filter" data-discussion-search>
                    </form>
                </nav>

                <section class="discussion-feed-list">
                    @foreach ($discussions as $discussion)
                        @php
                            $replyCount = (int) ($discussion['replies'] ?? 0);
                            $views = (int) ($discussion['views'] ?? 0);
                            $helpfulVotes = (int) ($discussion['helpful_votes'] ?? 0);
                            $isOwn = ($discussion['author'] ?? '') === $displayName;
                            $imageCount = (int) ($discussion['image_count'] ?? 0);
                            $badge = $replyCount === 0 ? 'Question' : ($discussion['has_image'] ? $imageCount.' '.Str::plural('image', $imageCount) : 'Discussion');
                        @endphp
                        <article
                            class="discussion-feed-card {{ ! empty($discussion['has_image']) ? 'has-image' : '' }} {{ $isOwn ? 'is-own' : '' }}"
                            data-discussion-row
                            data-title="{{ strtolower($discussion['title']) }}"
                            data-body="{{ strtolower($discussion['body'] ?? '') }}"
                            data-author="{{ strtolower($discussion['author']) }}"
                            data-group="{{ strtolower($discussion['group']) }}"
                            data-views="{{ $views }}"
                            data-replies="{{ $replyCount }}"
                            data-helpful="{{ $helpfulVotes }}"
                            data-has-image="{{ ! empty($discussion['has_image']) ? '1' : '0' }}"
                            data-own="{{ $isOwn ? '1' : '0' }}"
                            data-my-group="{{ in_array((string) ($discussion['group_id'] ?? ''), $joinedGroupIds, true) ? '1' : '0' }}"
                            data-thread-url="{{ route('studyhub.student.discussions.show', $discussion['id']) }}"
                        >
                            <aside class="discussion-vote-rail">
                                <form class="discussion-helpful-form" method="POST" action="{{ route('studyhub.student.discussions.helpful', $discussion['id']) }}">
                                    @csrf
                                    <button
                                        class="discussion-helpful-button {{ ! empty($discussion['viewer_voted_helpful']) ? 'is-active' : '' }}"
                                        type="submit"
                                        aria-label="{{ ! empty($discussion['viewer_voted_helpful']) ? 'Remove helpful vote' : 'Mark as helpful' }}"
                                        data-loading-label="Saving..."
                                        data-outline-icon="{{ asset('images/up arrow.png') }}"
                                        data-filled-icon="{{ asset('images/fill up.png') }}"
                                    >
                                        <span class="discussion-helpful-icon-box">
                                            <img
                                                class="discussion-helpful-icon"
                                                src="{{ asset(! empty($discussion['viewer_voted_helpful']) ? 'images/fill up.png' : 'images/up arrow.png') }}"
                                                alt=""
                                                aria-hidden="true"
                                            >
                                        </span>
                                        <strong data-helpful-count>{{ $helpfulVotes }}</strong>
                                    </button>
                                </form>
                                <small>helpful</small>
                            </aside>

                            <div class="discussion-post-body">
                                <div class="discussion-post-meta">
                                    <span class="discussion-author-avatar small">
                                        @if (! empty($discussion['author_avatar_url']))
                                            <img src="{{ $discussion['author_avatar_url'] }}" alt="{{ $discussion['author'] }}">
                                        @else
                                            {{ $discussion['author_initials'] ?? '??' }}
                                        @endif
                                    </span>
                                    <strong>{{ $discussion['author'] }}</strong>
                                    @if ($isOwn)
                                        <span class="discussion-you-badge">You</span>
                                    @endif
                                    <span>{{ $discussion['last_active'] }}</span>
                                    <span class="discussion-group-pill">{{ $discussion['group'] }}</span>
                                    <span class="discussion-type-pill">{{ $badge }}</span>
                                </div>

                                <a class="discussion-post-title" href="{{ route('studyhub.student.discussions.show', $discussion['id']) }}">{{ $discussion['title'] }}</a>
                                <p>{{ Str::limit($discussion['body'] ?? '', 150) }}</p>

                                <div class="discussion-post-tags">
                                    <span>#{{ Str::of($discussion['group'])->replace(' ', '') }}</span>
                                    @if (! empty($discussion['has_image']))
                                        <span>#Image</span>
                                    @endif
                                </div>

                                <div class="discussion-post-stats">
                                    <span>{!! $icons['discussion'] !!} {{ $replyCount }} {{ Str::plural('Reply', $replyCount) }}</span>
                                    <span>{!! $icons['eye'] !!} {{ $views }} Views</span>
                                </div>
                            </div>

                            @if (! empty($discussion['has_image']))
                                <a class="discussion-image-preview {{ $imageCount > 1 ? 'is-gallery' : '' }}" href="{{ route('studyhub.student.discussions.show', $discussion['id']) }}">
                                    @foreach (collect($discussion['images'] ?? [])->take(4) as $image)
                                        <img src="{{ $image['url'] }}" alt="{{ $image['name'] ?: $discussion['title'] }}">
                                    @endforeach
                                    @if ($imageCount > 1)
                                        <span>{{ $imageCount }}</span>
                                    @endif
                                </a>
                            @endif

                            @if ($isOwn)
                                <div class="discussion-card-menu">
                                    <button class="discussion-card-menu-toggle" type="button" aria-expanded="false" aria-label="Post options" data-discussion-card-menu-toggle>...</button>
                                    <form class="discussion-delete-menu" method="POST" action="{{ route('studyhub.student.discussions.delete', $discussion['id']) }}" onsubmit="return confirm('Delete this discussion and its replies?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" data-loading-label="Deleting...">Delete</button>
                                    </form>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </section>

                <div class="discussion-empty-state app-empty-state hidden" data-discussion-empty>
                    <span class="app-empty-icon">{!! $icons['discussion'] !!}</span>
                    <strong data-empty-title>No discussions found</strong>
                    <span data-empty-copy>Try another search or start a new post.</span>
                    <button class="app-empty-action" type="button" data-discussion-focus>Create post</button>
                </div>

                @if (method_exists($discussions, 'links'))
                    <div class="mt-6" data-discussion-pagination>
                        {{ $discussions->links() }}
                    </div>
                @endif
            </main>

            <aside class="discussion-feed-sidebar">
                <section class="discussion-side-card">
                    <h3>{!! $icons['trend'] !!}<span>Trending Tags</span></h3>
                    <div class="discussion-tag-list">
                        @forelse ($trendingTags as $tag)
                            <span>#{{ Str::of($tag['name'])->replace(' ', '') }} <strong>{{ $tag['count'] }}</strong></span>
                        @empty
                            <p>No tags yet.</p>
                        @endforelse
                    </div>
                </section>

                <section class="discussion-side-card">
                    <h3>{!! $icons['message'] !!}<span>Unanswered Posts</span></h3>
                    <div class="discussion-question-list">
                        @forelse ($unanswered->take(4) as $question)
                            <a href="{{ route('studyhub.student.discussions.show', $question['id']) }}">
                                <strong>{{ Str::limit($question['title'], 42) }}</strong>
                                <span>{{ $question['group'] }} · {{ $question['last_active'] }}</span>
                            </a>
                        @empty
                            <p>Every visible post has a reply.</p>
                        @endforelse
                    </div>
                </section>

                <section class="discussion-side-card">
                    <h3>{!! $icons['settings'] !!}<span>How it works</span></h3>
                    <ul class="discussion-how-list">
                        <li>Ask a question or start a discussion.</li>
                        <li>Add images for clearer context.</li>
                        <li>Choose a group so the right classmates see it.</li>
                        <li>Open a post to reply and keep the thread moving.</li>
                    </ul>
                </section>
            </aside>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const composer = document.querySelector('[data-discussion-composer]');
            const composerTitle = composer?.querySelector('input[name="title"]');
            const composerBody = composer?.querySelector('textarea[name="body"]');
            const focusButtons = document.querySelectorAll('[data-discussion-focus]');
            const imageInput = document.querySelector('[data-discussion-image-input]');
            const imagePreview = document.querySelector('[data-discussion-image-preview]');
            const imagePreviewStack = document.querySelector('[data-discussion-image-preview-stack]');
            const imageName = document.querySelector('[data-discussion-image-name]');
            const imageSize = document.querySelector('[data-discussion-image-size]');
            const imageClear = document.querySelector('[data-discussion-image-clear]');
            const searchInput = document.querySelector('[data-discussion-search]');
            const tabButtons = Array.from(document.querySelectorAll('[data-discussion-tab]'));
            const discussionList = document.querySelector('.discussion-feed-list');
            const discussionRows = Array.from(document.querySelectorAll('[data-discussion-row]'));
            const emptyState = document.querySelector('[data-discussion-empty]');
            const pagination = document.querySelector('[data-discussion-pagination]');
            const helpfulForms = Array.from(document.querySelectorAll('.discussion-helpful-form'));
            const menuToggles = Array.from(document.querySelectorAll('[data-discussion-card-menu-toggle]'));
            let activeTab = '{{ $activeTab }}';

            discussionRows.forEach(function (row) {
                row.addEventListener('click', function (event) {
                    if (event.target.closest('a, button, input, select, textarea, form, label')) {
                        return;
                    }

                    const threadUrl = row.dataset.threadUrl;

                    if (threadUrl) {
                        window.location.href = threadUrl;
                    }
                });
            });

            focusButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    composer?.classList.add('is-expanded');
                    composer?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    composerTitle?.focus();
                });
            });

            [composerTitle, composerBody].forEach(function (field) {
                field?.addEventListener('focus', function () {
                    composer?.classList.add('is-expanded');
                });
            });

            composer?.addEventListener('click', function () {
                composer.classList.add('is-expanded');
            });

            let previewObjectUrls = [];

            const clearImagePreview = function () {
                previewObjectUrls.forEach(function (url) {
                    URL.revokeObjectURL(url);
                });
                previewObjectUrls = [];

                if (imageInput) {
                    imageInput.value = '';
                }

                if (imagePreviewStack) {
                    imagePreviewStack.innerHTML = '';
                }

                imagePreview?.classList.remove('is-visible');
                imagePreview?.setAttribute('hidden', 'hidden');
            };

            const compactFileName = function (name) {
                if (name.length <= 24) {
                    return name;
                }

                const parts = name.split('.');
                const extension = parts.length > 1 ? '.' + parts.pop() : '';
                return name.slice(0, 14) + '...' + extension;
            };

            const formatBytes = function (bytes) {
                if (! bytes) {
                    return 'Images selected';
                }

                if (bytes < 1024 * 1024) {
                    return Math.max(1, Math.round(bytes / 1024)) + ' KB';
                }

                return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            };

            imageInput?.addEventListener('change', function () {
                const files = Array.from(imageInput.files || []);

                if (files.length === 0) {
                    clearImagePreview();
                    return;
                }

                previewObjectUrls.forEach(function (url) {
                    URL.revokeObjectURL(url);
                });
                previewObjectUrls = [];

                if (imagePreviewStack) {
                    imagePreviewStack.innerHTML = '';
                    files.slice(0, 3).forEach(function (file) {
                        const imageUrl = URL.createObjectURL(file);
                        previewObjectUrls.push(imageUrl);

                        const image = document.createElement('img');
                        image.src = imageUrl;
                        image.alt = file.name;
                        imagePreviewStack.appendChild(image);
                    });
                }

                if (imageName) {
                    imageName.textContent = files.length === 1 ? compactFileName(files[0].name) : files.length + ' images selected';
                    imageName.title = files.map(function (file) {
                        return file.name;
                    }).join(', ');
                }

                if (imageSize) {
                    const totalBytes = files.reduce(function (total, file) {
                        return total + file.size;
                    }, 0);
                    imageSize.textContent = files.length === 1 ? formatBytes(totalBytes) : formatBytes(totalBytes) + ' total';
                }

                imagePreview?.removeAttribute('hidden');
                imagePreview?.classList.add('is-visible');
            });

            imageClear?.addEventListener('click', clearImagePreview);

            helpfulForms.forEach(function (form) {
                form.addEventListener('submit', async function (event) {
                    event.preventDefault();

                    const button = form.querySelector('.discussion-helpful-button');
                    const countNode = form.querySelector('[data-helpful-count]');
                    const icon = form.querySelector('.discussion-helpful-icon');
                    const row = form.closest('[data-discussion-row]');

                    if (! button || button.disabled) {
                        return;
                    }

                    button.disabled = true;

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: new FormData(form),
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (! response.ok) {
                            throw new Error('Helpful vote request failed.');
                        }

                        const data = await response.json();
                        const isActive = Boolean(data.viewer_voted_helpful);
                        const helpfulCount = Number(data.helpful_votes || 0);

                        button.classList.toggle('is-active', isActive);
                        button.setAttribute('aria-label', isActive ? 'Remove helpful vote' : 'Mark as helpful');

                        if (icon) {
                            icon.src = isActive ? button.dataset.filledIcon : button.dataset.outlineIcon;
                        }

                        if (countNode) {
                            countNode.textContent = String(helpfulCount);
                        }

                        if (row) {
                            row.dataset.helpful = String(helpfulCount);
                        }

                        applyDiscussionControls();
                    } catch (error) {
                        form.submit();
                    } finally {
                        button.disabled = false;
                    }
                });
            });

            menuToggles.forEach(function (toggle) {
                toggle.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const menu = toggle.closest('.discussion-card-menu');
                    const isOpen = menu?.classList.toggle('is-open') || false;
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            });

            document.addEventListener('click', function () {
                menuToggles.forEach(function (toggle) {
                    toggle.closest('.discussion-card-menu')?.classList.remove('is-open');
                    toggle.setAttribute('aria-expanded', 'false');
                });
            });

            const applyDiscussionControls = function () {
                const searchTerm = (searchInput?.value || '').trim().toLowerCase();
                let visibleCount = 0;

                discussionRows.forEach(function (row) {
                    const matchesSearch = searchTerm === ''
                        || row.dataset.title.includes(searchTerm)
                        || row.dataset.body.includes(searchTerm)
                        || row.dataset.author.includes(searchTerm)
                        || row.dataset.group.includes(searchTerm);
                    const matchesTab = activeTab === 'all'
                        || activeTab === 'latest'
                        || (activeTab === 'unanswered' && Number(row.dataset.replies) === 0)
                        || (activeTab === 'helpful')
                        || (activeTab === 'mine' && row.dataset.myGroup === '1');
                    const isVisible = matchesSearch && matchesTab;

                    row.classList.toggle('is-hidden', ! isVisible);

                    if (isVisible) {
                        visibleCount += 1;
                    }
                });

                const sortedRows = [...discussionRows].sort(function (a, b) {
                    if (activeTab === 'helpful') {
                        return Number(b.dataset.helpful) - Number(a.dataset.helpful);
                    }

                    if (activeTab === 'latest') {
                        return 0;
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
                    emptyCopy: 'Posts from your joined groups will appear here.',
                    filteredTitle: 'No discussions match your filters',
                    filteredCopy: 'Try another tab or search term.',
                });

                searchInput?.closest('form')?.classList.toggle('is-active', searchTerm !== '');
                pagination?.classList.toggle('is-hidden', activeTab !== 'all' || searchTerm !== '');
            };

            tabButtons.forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    activeTab = button.dataset.discussionTab || 'all';
                    tabButtons.forEach(function (tab) {
                        tab.classList.toggle('active', tab === button);
                    });
                    applyDiscussionControls();
                });
            });

            searchInput?.addEventListener('input', function () {
                applyDiscussionControls();
            });

            searchInput?.closest('form')?.addEventListener('submit', function (event) {
                event.preventDefault();
                applyDiscussionControls();
            });

            searchInput?.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                }
            });
            applyDiscussionControls();
        });
    </script>
@endsection
