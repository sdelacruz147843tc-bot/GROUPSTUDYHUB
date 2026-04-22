@extends('studyhub.student.layout')

@section('title', $group['name'])

@push('page-styles')
    <style>
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--student-accent);
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 18px;
        }

        .detail-hero {
            overflow: hidden;
            margin-bottom: 20px;
            border-radius: 18px;
        }

        .detail-status {
            margin: 0 0 16px;
            padding: 13px 15px;
            border-radius: 16px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 24%, white 76%);
            background: color-mix(in srgb, var(--student-accent-pale) 74%, white 26%);
            color: var(--student-accent-text);
            font-size: 0.94rem;
        }

        .detail-hero-banner {
            background: linear-gradient(180deg, color-mix(in srgb, var(--student-accent-soft) 82%, white 18%) 0%, var(--student-accent) 100%);
            color: white;
            padding: 22px 24px 18px;
        }

        .detail-hero-banner h2 {
            margin: 0 0 4px;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .detail-hero-banner p {
            margin: 0 0 12px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.92rem;
        }

        .detail-hero-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }

        .detail-hero-members {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.95);
        }

        .detail-hero-tags {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .detail-hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: white;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .detail-hero-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 148px;
            height: 40px;
            padding: 0 20px;
            border-radius: 10px;
            background: #ffffff;
            color: var(--student-accent-text);
            font-size: 0.92rem;
            font-weight: 800;
            box-shadow: 0 12px 24px rgba(28, 74, 43, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.78);
        }

        .detail-hero-button.joined {
            background: color-mix(in srgb, var(--student-accent-pale) 52%, white 48%);
            color: var(--student-accent-text);
            border-color: color-mix(in srgb, var(--student-accent) 24%, white 76%);
            box-shadow: 0 12px 24px color-mix(in srgb, var(--student-accent) 16%, transparent 84%);
        }

        .detail-hero-button.leave {
            cursor: pointer;
        }

        .detail-hero-join {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .detail-hero-code {
            width: 180px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.42);
            background: rgba(255, 255, 255, 0.14);
            color: white;
            padding: 0 14px;
            font: inherit;
        }

        .detail-hero-code::placeholder {
            color: rgba(255, 255, 255, 0.72);
        }

        .detail-hero-code-error {
            width: 100%;
            color: #fff2ed;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.55fr) minmax(260px, 0.95fr);
            gap: 18px;
        }

        .detail-panel {
            overflow: hidden;
            border-radius: 14px;
        }

        .detail-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 16px;
            background: linear-gradient(90deg, color-mix(in srgb, var(--student-accent-pale) 78%, white 22%) 0%, color-mix(in srgb, var(--student-accent-pale) 58%, white 42%) 100%);
            border-bottom: 1px solid color-mix(in srgb, var(--student-accent) 26%, white 74%);
        }

        .detail-panel-title {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            font-size: 1.35rem;
            font-weight: 800;
            color: #214130;
        }

        .detail-panel-title .icon-box {
            color: var(--student-accent-text);
        }

        .detail-panel-title svg {
            width: 20px;
            height: 20px;
        }

        .detail-panel-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            color: white;
            font-size: 0.82rem;
            font-weight: 700;
            border: 0;
            cursor: pointer;
        }

        .detail-panel-body {
            padding: 14px;
            display: grid;
            gap: 12px;
        }

        .resource-row,
        .session-card {
            border: 1px solid #dde7ef;
            border-radius: 10px;
            background: white;
        }

        .resource-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 12px 12px;
        }

        .resource-main {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex: 1;
        }

        .resource-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: color-mix(in srgb, var(--student-accent-pale) 82%, white 18%);
            color: var(--student-accent-text);
            flex: 0 0 auto;
        }

        .resource-copy {
            min-width: 0;
        }

        .resource-copy strong {
            display: block;
            margin-bottom: 4px;
            font-size: 0.92rem;
            color: #214130;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .resource-meta {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            font-size: 0.75rem;
            color: #7d858d;
        }

        .resource-empty {
            padding: 22px 18px;
            border: 1px dashed color-mix(in srgb, var(--student-accent) 24%, white 76%);
            border-radius: 14px;
            background: color-mix(in srgb, var(--student-accent-pale) 42%, white 58%);
            color: #5e6e65;
            font-size: 0.92rem;
            text-align: center;
        }

        .resource-download {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 76px;
            height: 32px;
            padding: 0 12px;
            border-radius: 7px;
            background: color-mix(in srgb, var(--student-accent-pale) 74%, white 26%);
            color: var(--student-accent-text);
            font-size: 0.78rem;
            font-weight: 700;
            flex: 0 0 auto;
        }

        .session-card {
            padding: 14px 14px 12px;
        }

        .session-card h3 {
            margin: 0 0 10px;
            font-size: 0.96rem;
            line-height: 1.45;
            color: #34533d;
            font-weight: 800;
        }

        .session-card p {
            margin: 0 0 4px;
            color: #7d858d;
            font-size: 0.76rem;
        }

        .session-note {
            margin: 10px 0 0;
            color: #5e6e65;
            font-size: 0.78rem;
            line-height: 1.45;
        }

        .session-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .session-actions form {
            margin: 0;
            flex: 1;
        }

        .session-rsvp,
        .session-detail-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 7px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .session-rsvp {
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            color: white;
            border: 0;
            cursor: pointer;
        }

        .session-rsvp.joined {
            background: color-mix(in srgb, var(--student-accent-pale) 72%, white 28%);
            color: var(--student-accent-text);
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            cursor: default;
        }

        .session-detail-button {
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background: rgba(255,255,255,0.94);
            color: #34493b;
            cursor: pointer;
        }

        .schedule-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            border-radius: 8px;
            background: color-mix(in srgb, var(--student-accent-pale) 74%, white 26%);
            color: var(--student-accent-text);
            font-size: 0.8rem;
            font-weight: 700;
            padding: 0 14px;
            justify-self: center;
        }

        .session-empty {
            padding: 18px 16px;
            border: 1px dashed color-mix(in srgb, var(--student-accent) 24%, white 76%);
            border-radius: 12px;
            background: color-mix(in srgb, var(--student-accent-pale) 42%, white 58%);
            color: #5e6e65;
            font-size: 0.88rem;
            text-align: center;
        }

        .detail-upload-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
            z-index: 40;
        }

        .detail-upload-modal.is-open {
            display: flex;
        }

        .detail-upload-backdrop {
            position: absolute;
            inset: 0;
            border: 0;
            background: rgba(15, 22, 17, 0.48);
            backdrop-filter: blur(8px);
            cursor: pointer;
        }

        .detail-upload-panel {
            position: relative;
            z-index: 1;
            width: min(560px, 100%);
            border-radius: 26px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background:
                radial-gradient(circle at top right, color-mix(in srgb, var(--student-accent-pale) 78%, white 22%), transparent 36%),
                linear-gradient(180deg, color-mix(in srgb, var(--student-accent-pale) 40%, white 60%) 0%, rgba(255,255,255,0.99) 100%);
            box-shadow: 0 28px 56px color-mix(in srgb, var(--student-accent-text) 18%, transparent 82%);
            overflow: hidden;
        }

        .detail-upload-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 20px 22px 14px;
            border-bottom: 1px solid color-mix(in srgb, var(--student-accent) 16%, white 84%);
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 70%, white 30%) 0%, color-mix(in srgb, var(--student-accent-pale) 74%, white 26%) 100%);
        }

        .detail-upload-title {
            margin: 0 0 4px;
            font-size: 1.55rem;
            line-height: 1.15;
            font-weight: 800;
            color: #183425;
        }

        .detail-upload-copy {
            margin: 0;
            color: color-mix(in srgb, var(--student-accent-text) 72%, white 28%);
            font-size: 0.9rem;
        }

        .detail-upload-close {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background: color-mix(in srgb, var(--student-accent-pale) 54%, white 46%);
            color: var(--student-accent-text);
            font-size: 1.35rem;
            line-height: 1;
            cursor: pointer;
            flex-shrink: 0;
        }

        .detail-upload-body {
            padding: 18px 22px 22px;
        }

        .detail-upload-status,
        .detail-upload-errors {
            margin: 0 0 16px;
            padding: 13px 15px;
            border-radius: 16px;
            font-size: 0.92rem;
        }

        .detail-upload-status {
            border: 1px solid color-mix(in srgb, var(--student-accent) 24%, white 76%);
            background: color-mix(in srgb, var(--student-accent-pale) 74%, white 26%);
            color: var(--student-accent-text);
        }

        .detail-upload-errors {
            border: 1px solid rgba(219, 137, 120, 0.22);
            background: rgba(255, 244, 240, 0.92);
            color: #8a3f32;
        }

        .detail-upload-errors ul {
            margin: 8px 0 0;
            padding-left: 18px;
        }

        .detail-upload-form {
            display: grid;
            gap: 14px;
        }

        .detail-upload-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 15px;
            border-radius: 20px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background: linear-gradient(180deg, color-mix(in srgb, var(--student-accent-pale) 48%, white 52%) 0%, color-mix(in srgb, var(--student-accent-pale) 22%, white 78%) 100%);
        }

        .detail-upload-label {
            font-size: 0.92rem;
            font-weight: 700;
            color: #244231;
        }

        .detail-upload-select,
        .detail-upload-file {
            width: 100%;
            border-radius: 16px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 14%, white 86%);
            background: rgba(255,255,255,0.94);
            color: #1f3528;
            font: inherit;
        }

        .detail-upload-select {
            height: 52px;
            padding: 0 16px;
        }

        .detail-upload-file {
            padding: 14px 16px;
        }

        .detail-upload-actions {
            display: flex;
            justify-content: flex-end;
            padding-top: 4px;
        }

        .detail-upload-submit {
            min-height: 50px;
            min-width: 170px;
            padding: 0 22px;
            border-radius: 16px;
            border: none;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            color: white;
            font-size: 0.95rem;
            font-weight: 800;
            box-shadow: 0 14px 28px color-mix(in srgb, var(--student-accent) 24%, transparent 76%);
            cursor: pointer;
        }

        .detail-session-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
            z-index: 41;
        }

        .detail-session-modal.is-open {
            display: flex;
        }

        .detail-session-backdrop {
            position: absolute;
            inset: 0;
            border: 0;
            background: rgba(15, 22, 17, 0.48);
            backdrop-filter: blur(8px);
            cursor: pointer;
        }

        .detail-session-panel,
        .detail-session-details-panel {
            position: relative;
            z-index: 1;
            width: min(620px, 100%);
            border-radius: 26px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background:
                radial-gradient(circle at top right, color-mix(in srgb, var(--student-accent-pale) 78%, white 22%), transparent 36%),
                linear-gradient(180deg, color-mix(in srgb, var(--student-accent-pale) 40%, white 60%) 0%, rgba(255,255,255,0.99) 100%);
            box-shadow: 0 28px 56px color-mix(in srgb, var(--student-accent-text) 18%, transparent 82%);
            overflow: hidden;
        }

        .detail-session-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 20px 22px 14px;
            border-bottom: 1px solid color-mix(in srgb, var(--student-accent) 16%, white 84%);
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 70%, white 30%) 0%, color-mix(in srgb, var(--student-accent-pale) 74%, white 26%) 100%);
        }

        .detail-session-title {
            margin: 0 0 4px;
            font-size: 1.55rem;
            line-height: 1.15;
            font-weight: 800;
            color: #183425;
        }

        .detail-session-copy {
            margin: 0;
            color: color-mix(in srgb, var(--student-accent-text) 72%, white 28%);
            font-size: 0.9rem;
        }

        .detail-session-close {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background: color-mix(in srgb, var(--student-accent-pale) 54%, white 46%);
            color: var(--student-accent-text);
            font-size: 1.35rem;
            line-height: 1;
            cursor: pointer;
            flex-shrink: 0;
        }

        .detail-session-body {
            padding: 18px 22px 22px;
        }

        .detail-session-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .detail-session-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 15px;
            border-radius: 20px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background: linear-gradient(180deg, color-mix(in srgb, var(--student-accent-pale) 48%, white 52%) 0%, color-mix(in srgb, var(--student-accent-pale) 22%, white 78%) 100%);
        }

        .detail-session-field.full {
            grid-column: 1 / -1;
        }

        .detail-session-label {
            font-size: 0.92rem;
            font-weight: 700;
            color: #244231;
        }

        .detail-session-input,
        .detail-session-select {
            width: 100%;
            height: 52px;
            padding: 0 16px;
            border-radius: 16px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 14%, white 86%);
            background: rgba(255,255,255,0.94);
            color: #1f3528;
            font: inherit;
        }

        .detail-session-submit-row {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
            padding-top: 4px;
        }

        .detail-session-submit {
            min-height: 50px;
            min-width: 170px;
            padding: 0 22px;
            border-radius: 16px;
            border: none;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            color: white;
            font-size: 0.95rem;
            font-weight: 800;
            box-shadow: 0 14px 28px color-mix(in srgb, var(--student-accent) 24%, transparent 76%);
            cursor: pointer;
        }

        .detail-session-details-grid {
            display: grid;
            gap: 12px;
        }

        .detail-session-details-card {
            padding: 15px;
            border-radius: 18px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background: linear-gradient(180deg, color-mix(in srgb, var(--student-accent-pale) 42%, white 58%) 0%, rgba(255,255,255,0.94) 100%);
        }

        .detail-session-details-card strong {
            display: block;
            margin-bottom: 6px;
            color: #183425;
        }

        .detail-session-details-card span {
            color: #5f7267;
            line-height: 1.5;
        }

        @media (max-width: 980px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 680px) {
            .resource-row {
                flex-direction: column;
                align-items: stretch;
            }

            .resource-download {
                width: 100%;
            }

            .detail-upload-modal {
                padding: 16px;
            }

            .detail-session-modal,
            .detail-session-form {
                padding: 16px;
            }

            .detail-session-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('page')
    @php
        $showUploadModal = $errors->has('group_id') || $errors->has('category') || $errors->has('resource_file');
        $showSessionModal = $errors->has('title') || $errors->has('date') || $errors->has('start_time') || $errors->has('end_time') || $errors->has('location') || $errors->has('type') || $errors->has('max_attendees');
    @endphp

    <a class="back-link" href="{{ route('studyhub.student.groups') }}">
        <span class="icon-box">{!! $icons['arrow-left'] !!}</span>
        <span>Back to Groups</span>
    </a>

    <section class="content-card detail-hero">
        <div class="detail-hero-banner">
            <h2>{{ $group['name'] }}</h2>
            <p>{{ $group['description'] }}</p>
            <div class="detail-hero-meta">
                <div class="detail-hero-tags">
                    <div class="detail-hero-members">
                        <span class="icon-box">{!! $icons['users'] !!}</span>
                        <span>{{ $group['members'] }} members</span>
                    </div>
                    @if (($group['visibility'] ?? 'public') === 'private')
                        <span class="detail-hero-tag">
                            <span class="icon-box">{!! $icons['lock'] !!}</span>
                            <span>Private</span>
                        </span>
                    @endif
                </div>
                @if ($isJoined)
                    <form method="POST" action="{{ route('studyhub.student.groups.leave', $group['id']) }}">
                        @csrf
                        <button class="detail-hero-button joined leave" type="submit">Leave Group</button>
                    </form>
                @else
                    <form class="detail-hero-join" method="POST" action="{{ route('studyhub.student.groups.join', $group['id']) }}">
                        @csrf
                        @if (($group['visibility'] ?? 'public') === 'private')
                            <input class="detail-hero-code" type="text" name="join_code" placeholder="Enter join code" value="{{ old('join_code') }}">
                        @endif
                        <button class="detail-hero-button" type="submit">Join Group</button>
                        @if (! empty($joinCodeError))
                            <div class="detail-hero-code-error">{{ $joinCodeError }}</div>
                        @endif
                    </form>
                @endif
            </div>
        </div>
    </section>

    <section class="detail-grid">
        <article class="content-card detail-panel">
            <div class="detail-panel-header">
                <div class="detail-panel-title">
                    <span class="icon-box">{!! $icons['file'] !!}</span>
                    <span>Shared Resources</span>
                </div>
                @if ($isJoined)
                    <button class="detail-panel-action" type="button" data-detail-upload-open>
                        <span class="icon-box">{!! $icons['plus'] !!}</span>
                        <span>Upload</span>
                    </button>
                @else
                    <span class="detail-panel-action" style="opacity: 0.72; cursor: default;">
                        <span>Join to upload</span>
                    </span>
                @endif
            </div>

            <div class="detail-panel-body">
                @forelse ($resources as $resource)
                    <div class="resource-row">
                        <div class="resource-main">
                            <div class="icon-box resource-icon">{!! $icons['file'] !!}</div>
                            <div class="resource-copy">
                                <strong>{{ $resource['name'] }}</strong>
                                <div class="resource-meta">
                                    <span>Uploaded by {{ $resource['uploaded_by'] ?? 'StudyHub Member' }}</span>
                                    @if (! empty($resource['size']))
                                        <span>{{ $resource['size'] }}</span>
                                    @endif
                                    <span>{{ $resource['date'] }}</span>
                                </div>
                            </div>
                        </div>
                        <a class="resource-download" href="{{ ! empty($resource['path']) ? asset('storage/'.$resource['path']) : '#' }}" @if (! empty($resource['path'])) download @endif>Download</a>
                    </div>
                @empty
                    <div class="resource-empty">No resources yet. Upload the first file for this group.</div>
                @endforelse
            </div>
        </article>

        <article class="content-card detail-panel">
            <div class="detail-panel-header">
                <div class="detail-panel-title">
                    <span class="icon-box">{!! $icons['calendar'] !!}</span>
                    <span>Upcoming Sessions</span>
                </div>
                @if ($isJoined)
                    <button class="detail-panel-action" type="button" data-detail-session-open>
                        <span class="icon-box">{!! $icons['plus'] !!}</span>
                        <span>Schedule</span>
                    </button>
                @endif
            </div>

            <div class="detail-panel-body">
                @forelse ($sessions as $session)
                    @php
                        $isSessionJoined = in_array($studentProfile['display_name'], $session['attendee_names'] ?? [], true);
                        $isSessionFull = (int) $session['attendees'] >= (int) $session['max_attendees'];
                    @endphp
                    <div class="session-card">
                        <h3>{{ $session['title'] }}</h3>
                        <p>{{ $session['date'] }} at {{ $session['time'] }}</p>
                        <p>{{ $session['location'] }}</p>
                        <p>{{ $session['attendees'] }} / {{ $session['max_attendees'] }} attendees</p>
                        @if (! empty($session['notes']))
                            <p class="session-note">{{ $session['notes'] }}</p>
                        @endif
                        <div class="session-actions">
                            @if ($isSessionJoined)
                                <span class="session-rsvp joined">{{ $session['type'] === 'online' ? 'Joined' : 'RSVPd' }}</span>
                            @else
                                <form method="POST" action="{{ route('studyhub.student.sessions.rsvp', $session['id']) }}">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="{{ route('studyhub.student.group.show', $group['id']) }}">
                                    <button class="session-rsvp" type="submit" @if ($isSessionFull) disabled @endif>{{ $isSessionFull ? 'Full' : ($session['type'] === 'online' ? 'Join' : 'RSVP') }}</button>
                                </form>
                            @endif
                            <button
                                class="session-detail-button"
                                type="button"
                                data-detail-session-details
                                data-session-title="{{ $session['title'] }}"
                                data-session-group="{{ $session['group'] }}"
                                data-session-date="{{ $session['date'] }}"
                                data-session-time="{{ $session['time'] }}"
                                data-session-location="{{ $session['location'] }}"
                                data-session-type="{{ ucfirst($session['type']) }}"
                                data-session-attendees="{{ $session['attendees'] }} / {{ $session['max_attendees'] }}"
                                data-session-host="{{ $session['created_by'] ?? 'StudyHub Member' }}"
                                data-session-notes="{{ $session['notes'] ?? 'No extra notes yet.' }}"
                            >Details</button>
                        </div>
                    </div>
                @empty
                    <div class="session-empty">No sessions yet. Schedule the first group session.</div>
                @endforelse

                @if ($isJoined)
                    <button class="schedule-button" type="button" data-detail-session-open>Schedule New Session</button>
                @endif
            </div>
        </article>
    </section>

    @if ($isJoined)
        <div class="detail-session-modal @if ($showSessionModal) is-open @endif" data-detail-session-modal>
            <button class="detail-session-backdrop" type="button" aria-label="Close session form" data-detail-session-close></button>
            <div class="detail-session-panel">
                <div class="detail-session-header">
                    <div>
                        <h3 class="detail-session-title">Schedule session</h3>
                        <p class="detail-session-copy">{{ $group['name'] }}</p>
                    </div>
                    <button class="detail-session-close" type="button" aria-label="Close session form" data-detail-session-close>&times;</button>
                </div>

                <div class="detail-session-body">
                    @if ($showSessionModal)
                        <div class="detail-upload-errors">
                            Please fix the following:
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="detail-session-form" method="POST" action="{{ route('studyhub.student.sessions.store') }}">
                        @csrf
                        <input type="hidden" name="group_id" value="{{ $group['id'] }}">
                        <input type="hidden" name="redirect_to" value="{{ route('studyhub.student.group.show', $group['id']) }}">

                        <label class="detail-session-field full">
                            <span class="detail-session-label">Title</span>
                            <input class="detail-session-input" type="text" name="title" maxlength="120" value="{{ old('title') }}" placeholder="Algorithms Review Session" required>
                        </label>

                        <label class="detail-session-field">
                            <span class="detail-session-label">Type</span>
                            <select class="detail-session-select" name="type" required>
                                <option value="">Choose type</option>
                                <option value="in-person" @selected(old('type') === 'in-person')>In person</option>
                                <option value="online" @selected(old('type') === 'online')>Online</option>
                            </select>
                        </label>

                        <label class="detail-session-field">
                            <span class="detail-session-label">Location</span>
                            <input class="detail-session-input" type="text" name="location" maxlength="120" value="{{ old('location') }}" placeholder="Library Room 204 or Zoom" required>
                        </label>

                        <label class="detail-session-field">
                            <span class="detail-session-label">Date</span>
                            <input class="detail-session-input" type="date" name="date" value="{{ old('date') }}" required>
                        </label>

                        <label class="detail-session-field">
                            <span class="detail-session-label">Max attendees</span>
                            <input class="detail-session-input" type="number" name="max_attendees" min="2" max="100" value="{{ old('max_attendees', 12) }}" required>
                        </label>

                        <label class="detail-session-field">
                            <span class="detail-session-label">Start time</span>
                            <input class="detail-session-input" type="time" name="start_time" value="{{ old('start_time') }}" required>
                        </label>

                        <label class="detail-session-field">
                            <span class="detail-session-label">End time</span>
                            <input class="detail-session-input" type="time" name="end_time" value="{{ old('end_time') }}" required>
                        </label>

                        <div class="detail-session-submit-row">
                            <button class="detail-session-submit" type="submit">Create Session</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="detail-session-modal" data-detail-session-details-modal>
            <button class="detail-session-backdrop" type="button" aria-label="Close session details" data-detail-session-details-close></button>
            <div class="detail-session-details-panel">
                <div class="detail-session-header">
                    <div>
                        <h3 class="detail-session-title" data-detail-session-title>Session Details</h3>
                        <p class="detail-session-copy" data-detail-session-group></p>
                    </div>
                    <button class="detail-session-close" type="button" aria-label="Close session details" data-detail-session-details-close>&times;</button>
                </div>

                <div class="detail-session-body">
                    <div class="detail-session-details-grid">
                        <div class="detail-session-details-card">
                            <strong>Date & Time</strong>
                            <span><span data-detail-session-date></span> | <span data-detail-session-time></span></span>
                        </div>
                        <div class="detail-session-details-card">
                            <strong>Location</strong>
                            <span data-detail-session-location></span>
                        </div>
                        <div class="detail-session-details-card">
                            <strong>Type</strong>
                            <span data-detail-session-type></span>
                        </div>
                        <div class="detail-session-details-card">
                            <strong>Attendees</strong>
                            <span data-detail-session-attendees></span>
                        </div>
                        <div class="detail-session-details-card">
                            <strong>Host</strong>
                            <span data-detail-session-host></span>
                        </div>
                        <div class="detail-session-details-card">
                            <strong>Notes</strong>
                            <span data-detail-session-notes></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-upload-modal @if ($showUploadModal) is-open @endif" data-detail-upload-modal>
            <button class="detail-upload-backdrop" type="button" aria-label="Close upload form" data-detail-upload-close></button>
            <div class="detail-upload-panel">
                <div class="detail-upload-header">
                    <div>
                        <h3 class="detail-upload-title">Upload file</h3>
                        <p class="detail-upload-copy">{{ $group['name'] }}</p>
                    </div>
                    <button class="detail-upload-close" type="button" aria-label="Close upload form" data-detail-upload-close>&times;</button>
                </div>

                <div class="detail-upload-body">
                    @if ($showUploadModal)
                        <div class="detail-upload-errors">
                            Please fix the following:
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="detail-upload-form" method="POST" action="{{ route('studyhub.student.resources.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="group_id" value="{{ $group['id'] }}">
                        <input type="hidden" name="redirect_to" value="{{ route('studyhub.student.group.show', $group['id']) }}">

                        <label class="detail-upload-field">
                            <span class="detail-upload-label">Category</span>
                            <select class="detail-upload-select" name="category" required>
                                <option value="">Choose category</option>
                                @foreach ($resourceCategories as $category)
                                    <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="detail-upload-field">
                            <span class="detail-upload-label">File</span>
                            <input class="detail-upload-file" type="file" name="resource_file" required>
                        </label>

                        <div class="detail-upload-actions">
                            <button class="detail-upload-submit" type="submit">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const uploadModal = document.querySelector('[data-detail-upload-modal]');
                const uploadOpenButton = document.querySelector('[data-detail-upload-open]');
                const uploadCloseButtons = document.querySelectorAll('[data-detail-upload-close]');
                const sessionModal = document.querySelector('[data-detail-session-modal]');
                const sessionOpenButtons = document.querySelectorAll('[data-detail-session-open]');
                const sessionCloseButtons = document.querySelectorAll('[data-detail-session-close]');
                const detailsModal = document.querySelector('[data-detail-session-details-modal]');
                const detailsOpenButtons = document.querySelectorAll('[data-detail-session-details]');
                const detailsCloseButtons = document.querySelectorAll('[data-detail-session-details-close]');

                const setBodyOverflow = function () {
                    const hasOpenModal = uploadModal?.classList.contains('is-open')
                        || sessionModal?.classList.contains('is-open')
                        || detailsModal?.classList.contains('is-open');
                    document.body.style.overflow = hasOpenModal ? 'hidden' : '';
                };

                const setModalState = function (modal, isOpen) {
                    if (! modal) {
                        return;
                    }

                    modal.classList.toggle('is-open', isOpen);
                    setBodyOverflow();
                };

                uploadOpenButton?.addEventListener('click', function () {
                    setModalState(uploadModal, true);
                });

                uploadCloseButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        setModalState(uploadModal, false);
                    });
                });

                sessionOpenButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        setModalState(sessionModal, true);
                    });
                });

                sessionCloseButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        setModalState(sessionModal, false);
                    });
                });

                detailsOpenButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        detailsModal.querySelector('[data-detail-session-title]').textContent = button.dataset.sessionTitle || 'Session Details';
                        detailsModal.querySelector('[data-detail-session-group]').textContent = button.dataset.sessionGroup || '';
                        detailsModal.querySelector('[data-detail-session-date]').textContent = button.dataset.sessionDate || '';
                        detailsModal.querySelector('[data-detail-session-time]').textContent = button.dataset.sessionTime || '';
                        detailsModal.querySelector('[data-detail-session-location]').textContent = button.dataset.sessionLocation || '';
                        detailsModal.querySelector('[data-detail-session-type]').textContent = button.dataset.sessionType || '';
                        detailsModal.querySelector('[data-detail-session-attendees]').textContent = button.dataset.sessionAttendees || '';
                        detailsModal.querySelector('[data-detail-session-host]').textContent = button.dataset.sessionHost || '';
                        detailsModal.querySelector('[data-detail-session-notes]').textContent = button.dataset.sessionNotes || '';
                        setModalState(detailsModal, true);
                    });
                });

                detailsCloseButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        setModalState(detailsModal, false);
                    });
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        setModalState(uploadModal, false);
                        setModalState(sessionModal, false);
                        setModalState(detailsModal, false);
                    }
                });

                setBodyOverflow();
            });
        </script>
    @endif
@endsection
