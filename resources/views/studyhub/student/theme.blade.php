@extends('studyhub.student.layout')

@section('title', 'Theme')

@push('page-styles')
    <style>
        .theme-page-header {
            margin-bottom: 24px;
        }

        .theme-page-header .page-title {
            letter-spacing: -0.04em;
        }

        .theme-page-header .page-subtitle {
            max-width: 760px;
            margin-bottom: 0;
        }

        .theme-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
            gap: 22px;
        }

        .theme-card {
            padding: 22px;
            border-radius: 24px;
            border: 1px solid rgba(195, 215, 203, 0.92);
            background: linear-gradient(180deg, rgba(255,255,255,0.97) 0%, rgba(248,252,249,0.99) 100%);
        }

        .theme-card + .theme-card {
            margin-top: 18px;
        }

        .theme-card h3 {
            margin: 0 0 8px;
            font-size: 1.45rem;
            letter-spacing: -0.03em;
            color: #173223;
        }

        .theme-card-copy {
            margin: 0 0 18px;
            color: #66776d;
            line-height: 1.55;
        }

        .option-grid {
            display: grid;
            gap: 12px;
        }

        .option-grid.two-column {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .option-card {
            position: relative;
            display: block;
            cursor: pointer;
        }

        .option-card input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .option-card-body {
            height: 100%;
            padding: 16px;
            border-radius: 18px;
            border: 1px solid rgba(197, 216, 204, 0.95);
            background: rgba(255,255,255,0.94);
            transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
        }

        .option-card:hover .option-card-body {
            transform: translateY(-2px);
            box-shadow: 0 16px 28px rgba(71, 101, 82, 0.08);
        }

        .option-card input:checked + .option-card-body {
            border-color: var(--student-accent-soft);
            box-shadow: 0 18px 30px rgba(71, 101, 82, 0.12);
            background: color-mix(in srgb, var(--student-accent-pale) 62%, white 38%);
        }

        .option-card-title {
            margin: 0 0 6px;
            font-size: 1rem;
            font-weight: 800;
            color: #173223;
        }

        .option-card-copy {
            margin: 0;
            color: #6e7f74;
            font-size: 0.86rem;
            line-height: 1.5;
        }

        .theme-swatch {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
        }

        .theme-swatch span {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.7);
            box-shadow: 0 0 0 1px rgba(158, 177, 166, 0.35);
        }

        .theme-forest span:nth-child(1) { background: #2f7d4d; }
        .theme-forest span:nth-child(2) { background: #67d38b; }
        .theme-forest span:nth-child(3) { background: #dff6e3; }

        .theme-ocean span:nth-child(1) { background: #286da3; }
        .theme-ocean span:nth-child(2) { background: #64afdf; }
        .theme-ocean span:nth-child(3) { background: #deeffa; }

        .theme-sunset span:nth-child(1) { background: #b15f36; }
        .theme-sunset span:nth-child(2) { background: #e09362; }
        .theme-sunset span:nth-child(3) { background: #f9e1d3; }

        .theme-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .theme-actions button,
        .theme-actions a {
            min-height: 48px;
            padding: 0 18px;
            justify-content: center;
        }

        .theme-status {
            padding: 14px 16px;
            margin-bottom: 18px;
            border-radius: 16px;
            border: 1px solid color-mix(in srgb, var(--student-accent-soft) 42%, white 58%);
            background: color-mix(in srgb, var(--student-accent-pale) 78%, white 22%);
            color: var(--student-accent-text);
            font-weight: 700;
        }

        .theme-preview {
            padding: 22px;
            border-radius: 24px;
            border: 1px solid rgba(195, 215, 203, 0.92);
            background: linear-gradient(180deg, rgba(255,255,255,0.97) 0%, rgba(248,252,249,0.99) 100%);
            position: sticky;
            top: 26px;
        }

        .preview-shell {
            padding: 16px;
            border-radius: 22px;
            background: var(--student-page-bg);
            border: 1px solid rgba(198, 216, 205, 0.88);
        }

        .preview-sidebar {
            padding: 16px;
            border-radius: 18px;
            background: var(--student-sidebar-bg);
            color: white;
            margin-bottom: 14px;
        }

        .preview-chip {
            display: inline-flex;
            align-items: center;
            min-height: 32px;
            padding: 0 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.12);
            font-size: 0.78rem;
            font-weight: 700;
            margin: 0 8px 8px 0;
        }

        .preview-card {
            padding: 18px;
            border-radius: var(--student-card-radius);
            border: 1px solid var(--student-card-border);
            background: rgba(255,255,255,0.94);
            box-shadow: var(--student-card-shadow);
        }

        .preview-card h4 {
            margin: 0 0 8px;
            font-size: 1rem;
            color: #173223;
        }

        .preview-card p {
            margin: 0 0 16px;
            color: #69796f;
            line-height: 1.5;
            font-size: 0.9rem;
        }

        .preview-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 16px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--student-accent-soft) 0%, var(--student-accent) 100%);
            color: white;
            font-size: 0.86rem;
            font-weight: 800;
        }

        @media (max-width: 1100px) {
            .theme-layout {
                grid-template-columns: 1fr;
            }

            .theme-preview {
                position: static;
            }
        }

        @media (max-width: 760px) {
            .option-grid.two-column {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('page')
    <div class="theme-page-header">
        <h2 class="page-title">Theme</h2>
        <p class="page-subtitle">Control the visual style of your StudyHub student workspace separately from your personal profile details.</p>
    </div>

    <div class="theme-layout">
        <div>
            <form method="POST" action="{{ route('studyhub.student.theme.update') }}">
                @csrf
                @method('PUT')

                <section class="content-card theme-card">
                    <h3>Color Theme</h3>
                    <p class="theme-card-copy">Choose the overall mood for your student workspace.</p>

                    <div class="option-grid">
                        @foreach ($profileOptions['themes'] as $option)
                            <label class="option-card">
                                <input type="radio" name="theme" value="{{ $option['value'] }}" {{ old('theme', $studentProfileForm['theme']) === $option['value'] ? 'checked' : '' }}>
                                <span class="option-card-body">
                                    <span class="theme-swatch theme-{{ $option['value'] }}">
                                        <span></span><span></span><span></span>
                                    </span>
                                    <span class="option-card-title">{{ $option['label'] }}</span>
                                    <span class="option-card-copy">{{ $option['description'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="content-card theme-card">
                    <h3>Surface Style</h3>
                    <p class="theme-card-copy">Adjust how cards and panels feel across the student pages.</p>

                    <div class="option-grid two-column">
                        @foreach ($profileOptions['surface_styles'] as $option)
                            <label class="option-card">
                                <input type="radio" name="surface_style" value="{{ $option['value'] }}" {{ old('surface_style', $studentProfileForm['surface_style']) === $option['value'] ? 'checked' : '' }}>
                                <span class="option-card-body">
                                    <span class="option-card-title">{{ $option['label'] }}</span>
                                    <span class="option-card-copy">{{ $option['description'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="content-card theme-card">
                    <h3>Interface Density</h3>
                    <p class="theme-card-copy">Choose whether the layout feels roomy or more information-dense.</p>

                    <div class="option-grid two-column">
                        @foreach ($profileOptions['densities'] as $option)
                            <label class="option-card">
                                <input type="radio" name="interface_density" value="{{ $option['value'] }}" {{ old('interface_density', $studentProfileForm['interface_density']) === $option['value'] ? 'checked' : '' }}>
                                <span class="option-card-body">
                                    <span class="option-card-title">{{ $option['label'] }}</span>
                                    <span class="option-card-copy">{{ $option['description'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="theme-actions">
                        <button class="action-button" type="submit">Save Theme</button>
                        <a class="secondary-button" href="{{ route('studyhub.student.profile') }}">Back To Profile</a>
                    </div>
                </section>
            </form>

            <form method="POST" action="{{ route('studyhub.student.theme.update') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="reset_defaults" value="1">
                <section class="content-card theme-card">
                    <h3>Reset Appearance</h3>
                    <p class="theme-card-copy">Reset your colors, surfaces, and spacing back to the default StudyHub theme.</p>
                    <div class="theme-actions">
                        <button class="secondary-button" type="submit">Reset Theme</button>
                    </div>
                </section>
            </form>
        </div>

        <aside class="theme-preview">
            <h3>Theme Preview</h3>
            <p class="theme-card-copy">Your current appearance settings shape the student pages right away.</p>

            <div class="preview-shell">
                <div class="preview-sidebar">
                    <span class="preview-chip">{{ ucfirst($studentProfile['theme']) }}</span>
                    <span class="preview-chip">{{ ucfirst($studentProfile['surface_style']) }}</span>
                    <span class="preview-chip">{{ ucfirst($studentProfile['interface_density']) }}</span>
                </div>

                <div class="preview-card">
                    <h4>Student Workspace</h4>
                    <p>Cards, spacing, and accents update from the choices on this page.</p>
                    <span class="preview-button">Preview Style</span>
                </div>
            </div>
        </aside>
    </div>
@endsection
