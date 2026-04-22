@extends('studyhub.student.layout')

@section('title', 'Theme')

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

