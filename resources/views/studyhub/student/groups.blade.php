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

@push('page-styles')
    <style>
        .groups-toolbar {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 14px;
            margin-bottom: 26px;
        }

        .groups-toolbar .page-title {
            font-size: 2.9rem;
            margin-bottom: 6px;
            letter-spacing: -0.04em;
        }

        .groups-toolbar .page-subtitle {
            margin: 0;
            max-width: 700px;
            font-size: 1.05rem;
        }

        .groups-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 52px;
            padding: 0 26px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            box-shadow: 0 14px 28px color-mix(in srgb, var(--student-accent) 24%, transparent 76%);
            font-size: 1rem;
            font-weight: 800;
            white-space: nowrap;
            color: white;
            cursor: pointer;
            list-style: none;
            border: none;
        }

        .groups-create-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 28px;
            z-index: 40;
        }

        .groups-create-modal.is-open {
            display: flex;
        }

        .groups-create-backdrop {
            position: absolute;
            inset: 0;
            border: 0;
            background: rgba(15, 22, 17, 0.48);
            backdrop-filter: blur(8px);
            cursor: pointer;
        }

        .groups-create-panel {
            position: relative;
            z-index: 1;
            width: min(760px, 100%);
            max-height: calc(100vh - 44px);
            overflow: auto;
            padding: 0;
            border-radius: 30px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background:
                radial-gradient(circle at top right, color-mix(in srgb, var(--student-accent-pale) 78%, white 22%), transparent 36%),
                linear-gradient(180deg, color-mix(in srgb, var(--student-accent-pale) 46%, white 54%) 0%, color-mix(in srgb, var(--student-accent-pale) 18%, white 82%) 100%);
            box-shadow: 0 28px 56px color-mix(in srgb, var(--student-accent-text) 18%, transparent 82%);
        }

        .groups-create-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 24px 26px 18px;
            border-bottom: 1px solid color-mix(in srgb, var(--student-accent) 14%, white 86%);
            background:
                linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 72%, white 28%) 0%, color-mix(in srgb, var(--student-accent-pale) 68%, white 32%) 100%);
        }

        .groups-create-intro {
            display: grid;
            gap: 8px;
        }

        .groups-create-kicker {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: fit-content;
            min-height: 30px;
            padding: 0 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.72);
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            color: var(--student-accent-text);
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .groups-create-title {
            margin: 0;
            font-size: 1.95rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #1c3928;
        }

        .groups-create-copy {
            margin: 0;
            color: color-mix(in srgb, var(--student-accent-text) 72%, white 28%);
            line-height: 1.35;
            max-width: 420px;
            font-size: 0.98rem;
        }

        .groups-create-close {
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

        .groups-create-body {
            padding: 22px 26px 24px;
        }

        .groups-status,
        .groups-errors {
            margin-bottom: 14px;
            padding: 12px 14px;
            border-radius: 16px;
            font-size: 0.95rem;
        }

        .groups-status {
            border: 1px solid color-mix(in srgb, var(--student-accent) 24%, white 76%);
            background: color-mix(in srgb, var(--student-accent-pale) 74%, white 26%);
            color: var(--student-accent-text);
        }

        .groups-empty-state {
            display: none;
            padding: 40px 24px;
            border-radius: 22px;
            border: 1px dashed rgba(171, 198, 180, 0.9);
            background: rgba(255,255,255,0.72);
            color: #597063;
            text-align: center;
            font-weight: 600;
        }

        .groups-errors {
            border: 1px solid rgba(219, 137, 120, 0.22);
            background: rgba(255, 244, 240, 0.92);
            color: #8a3f32;
        }

        .groups-errors ul {
            margin: 8px 0 0;
            padding-left: 18px;
        }

        .groups-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .groups-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 18px;
            border-radius: 24px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            background: linear-gradient(180deg, color-mix(in srgb, var(--student-accent-pale) 48%, white 52%) 0%, color-mix(in srgb, var(--student-accent-pale) 22%, white 78%) 100%);
        }

        .groups-field-full {
            grid-column: 1 / -1;
        }

        .groups-label {
            font-size: 0.98rem;
            font-weight: 700;
            color: #244231;
        }

        .groups-input,
        .groups-select,
        .groups-textarea {
            width: 100%;
            border-radius: 18px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 14%, white 86%);
            background: rgba(255,255,255,0.9);
            color: #1f3528;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.84);
            transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease;
            font-size: 1rem;
        }

        .groups-input,
        .groups-select {
            height: 58px;
            padding: 0 18px;
        }

        .groups-input:focus,
        .groups-select:focus,
        .groups-textarea:focus {
            outline: none;
            border-color: color-mix(in srgb, var(--student-accent) 44%, white 56%);
            box-shadow:
                0 0 0 4px color-mix(in srgb, var(--student-accent-pale) 62%, white 38%),
                inset 0 1px 0 rgba(255,255,255,0.9);
        }

        .groups-textarea {
            min-height: 130px;
            padding: 16px 18px;
            resize: vertical;
        }

        .groups-form-actions {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 2px;
            padding-top: 4px;
        }

        .groups-submit {
            min-height: 56px;
            min-width: 190px;
            padding: 0 26px;
            border-radius: 18px;
            border: none;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            color: white;
            font-size: 1rem;
            font-weight: 800;
            box-shadow: 0 16px 28px color-mix(in srgb, var(--student-accent) 26%, transparent 74%);
            cursor: pointer;
        }

        .groups-style-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .groups-visibility-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .groups-style-option {
            position: relative;
        }

        .groups-style-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .groups-style-card {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
            min-height: 136px;
            padding: 16px;
            border-radius: 22px;
            border: 1px solid color-mix(in srgb, var(--student-accent) 12%, white 88%);
            background: linear-gradient(180deg, color-mix(in srgb, var(--student-accent-pale) 62%, white 38%) 0%, color-mix(in srgb, var(--student-accent-pale) 28%, white 72%) 100%);
            cursor: pointer;
            transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease, background 160ms ease;
        }

        .groups-style-option input:checked + .groups-style-card {
            border-color: color-mix(in srgb, var(--student-accent) 42%, white 58%);
            background: linear-gradient(180deg, color-mix(in srgb, var(--student-accent-soft) 58%, white 42%) 0%, color-mix(in srgb, var(--student-accent-pale) 58%, white 42%) 100%);
            box-shadow: 0 14px 26px color-mix(in srgb, var(--student-accent) 14%, transparent 86%);
        }

        .groups-style-option:hover .groups-style-card {
            transform: translateY(-1px);
        }

        .groups-style-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            flex-shrink: 0;
        }

        .groups-style-icon.in-person {
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-soft) 88%, white 12%) 0%, var(--student-accent) 100%);
        }

        .groups-style-icon.online {
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-pale) 20%, #4d8fd4 80%) 0%, color-mix(in srgb, var(--student-accent) 40%, #3b79c7 60%) 100%);
        }

        .groups-style-icon.hybrid {
            background: linear-gradient(135deg, color-mix(in srgb, var(--student-accent-pale) 18%, #f0b66d 82%) 0%, color-mix(in srgb, var(--student-accent) 24%, #d57a48 76%) 100%);
        }

        .groups-style-copy {
            display: grid;
            gap: 4px;
            min-width: 0;
        }

        .groups-style-copy strong {
            color: #1f392b;
            font-size: 1rem;
        }

        .groups-style-copy span {
            color: #728076;
            font-size: 0.88rem;
            line-height: 1.4;
        }

        .groups-join-hint {
            color: #6f7c73;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .group-card-tags {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .group-card-tag {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 0 12px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--student-accent-pale) 76%, white 24%);
            color: var(--student-accent-text);
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .group-card-tag.private {
            gap: 6px;
            background: color-mix(in srgb, var(--student-accent-pale) 48%, white 52%);
        }

        .groups-controls {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .groups-filter-select {
            min-width: 150px;
            height: 48px;
            border-radius: 14px;
            border: 1px solid rgba(193, 216, 201, 0.9);
            background: rgba(255,255,255,0.94);
            padding: 0 14px;
            font: inherit;
            color: #355242;
            box-shadow: 0 14px 26px rgba(80, 111, 95, 0.08);
        }

        .groups-controls .search-box {
            max-width: 700px;
            flex: 1 1 520px;
        }

        .groups-controls .search-box input {
            height: 52px;
            border-radius: 16px;
            background: rgba(255,255,255,0.94);
            border: 1px solid rgba(193, 216, 201, 0.9);
            box-shadow: 0 14px 26px rgba(80, 111, 95, 0.08);
        }

        .groups-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px 18px;
        }

        .group-card {
            overflow: hidden;
            position: relative;
            border-radius: 22px;
            border: 1px solid rgba(195, 215, 203, 0.92);
            background: linear-gradient(180deg, rgba(255,255,255,0.97) 0%, rgba(248,252,249,0.99) 100%);
            box-shadow: 0 24px 42px rgba(66, 95, 76, 0.1);
            transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
        }

        .group-card:hover {
            transform: translateY(-4px);
            border-color: rgba(141, 193, 158, 0.92);
            box-shadow: 0 30px 48px rgba(66, 95, 76, 0.14);
        }

        .group-card.is-hidden {
            display: none;
        }

        .group-card-cover {
            height: 132px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            color: white;
            background: linear-gradient(180deg, #9be3ae 0%, #8fdda5 100%);
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        .group-card-cover::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(180deg, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 100%),
                linear-gradient(0deg, rgba(10, 18, 12, 0.2) 0%, rgba(10, 18, 12, 0) 54%);
        }

        .group-card-cover::before {
            content: '';
            position: absolute;
            inset: auto 18px 16px auto;
            width: 76px;
            height: 76px;
            border-radius: 20px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.18);
            backdrop-filter: blur(4px);
        }

        .group-card-icon {
            position: relative;
            z-index: 1;
            width: 64px;
            height: 64px;
            border-radius: 22px;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.18);
            backdrop-filter: blur(6px);
        }

        .group-card-icon svg {
            width: 34px;
            height: 34px;
            stroke-width: 1.9;
        }

        .group-card-body {
            padding: 0;
        }

        .group-card-link {
            display: block;
            color: inherit;
        }

        .group-card-content {
            padding: 18px 18px 16px;
        }

        .group-card-title {
            margin: 0 0 12px;
            font-size: 1.50rem;
            line-height: 1.18;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #183425;
            min-height: 60px;
        }

        .group-card-copy {
            margin: 0 0 16px;
            color: #68776d;
            line-height: 1.5;
            font-size: 0.9rem;
            min-height: 44px;
        }

        .group-card-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            color: #6c7a70;
            font-size: 0.82rem;
            border-top: 1px solid rgba(226, 234, 229, 0.98);
            padding-top: 12px;
        }

        .group-card-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 0 18px 18px;
        }

        .group-open-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 16px;
            border-radius: 14px;
            background: color-mix(in srgb, var(--student-accent-pale) 68%, white 32%);
            color: var(--student-accent-text);
            font-size: 0.9rem;
            font-weight: 700;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
        }

        .group-join-form {
            margin: 0;
        }

        .group-join-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 16px;
            border-radius: 14px;
            border: none;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            color: white;
            font-size: 0.9rem;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 12px 22px color-mix(in srgb, var(--student-accent) 18%, transparent 82%);
        }

        .group-join-button.joined {
            background: color-mix(in srgb, var(--student-accent-pale) 72%, white 28%);
            color: var(--student-accent-text);
            box-shadow: none;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
            cursor: default;
        }

        .group-join-button.private {
            background: color-mix(in srgb, var(--student-accent-pale) 72%, white 28%);
            color: var(--student-accent-text);
            box-shadow: none;
            border: 1px solid color-mix(in srgb, var(--student-accent) 18%, white 82%);
        }

        .group-card-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }

        .group-card-meta-item .icon-box {
            color: #7d9185;
        }

        .group-card-meta-item svg {
            width: 14px;
            height: 14px;
            stroke-width: 2;
        }

        @media (max-width: 1100px) {
            .groups-toolbar .toolbar-actions {
                justify-content: flex-start;
            }

            .groups-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 800px) {
            .groups-toolbar .page-title {
                font-size: 2.3rem;
            }

            .groups-create-modal {
                padding: 18px;
            }

            .groups-create-header {
                align-items: flex-start;
            }

            .groups-create-body,
            .groups-create-header {
                padding-left: 18px;
                padding-right: 18px;
            }

            .groups-form {
                grid-template-columns: 1fr;
            }

            .groups-style-grid {
                grid-template-columns: 1fr;
            }

            .groups-visibility-grid {
                grid-template-columns: 1fr;
            }

            .groups-controls {
                align-items: stretch;
            }

            .groups-controls .search-box {
                max-width: none;
                flex-basis: 100%;
            }

            .groups-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

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
