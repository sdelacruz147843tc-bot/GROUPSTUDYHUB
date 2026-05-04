@extends('studyhub.student.layout')

@section('title', 'Theme')

@php
    $appearance = old('appearance', ($studentProfileForm['theme'] ?? 'forest') === 'dark' ? 'dark' : 'light');
@endphp

@section('page')
    <div class="theme-page-header">
        <h2 class="page-title">Theme</h2>
        <p class="page-subtitle">Switch your StudyHub workspace between light and dark mode.</p>
    </div>

    <div class="theme-layout theme-layout-simple">
        <div>
            <form method="POST" action="{{ route('studyhub.student.theme.update') }}" data-student-theme-form>
                @csrf
                @method('PUT')

                <section class="content-card theme-card theme-mode-card">
                    <div class="theme-section-head">
                        <h3>Appearance</h3>
                    </div>

                    <input name="appearance" type="hidden" value="{{ $appearance }}" data-student-theme-input>

                    <button class="student-theme-toggle {{ $appearance === 'dark' ? 'is-dark' : '' }}" type="button" aria-pressed="{{ $appearance === 'dark' ? 'true' : 'false' }}" data-student-theme-toggle>
                        <span class="student-theme-toggle-label" data-student-theme-label>{{ $appearance === 'dark' ? 'Dark' : 'Light' }}</span>
                        <span class="student-theme-toggle-track" aria-hidden="true">
                            <span class="student-theme-toggle-thumb"></span>
                        </span>
                    </button>

                    <div class="theme-actions">
                        <button class="action-button" type="submit" data-loading-label="Saving theme...">Save Theme</button>
                        <a class="secondary-button" href="{{ route('studyhub.student.profile') }}">Back To Profile</a>
                    </div>
                </section>
            </form>
        </div>

        <aside class="theme-preview">
            <h3>Preview</h3>

            <div class="preview-shell">
                <div class="preview-sidebar">
                    <span class="preview-chip" data-student-theme-preview>{{ ucfirst($appearance) }}</span>
                </div>

                <div class="preview-card">
                    <h4>Student Workspace</h4>
                    <span class="preview-button">Preview Style</span>
                </div>
            </div>
        </aside>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const shell = document.querySelector('.student-shell');
            const toggle = document.querySelector('[data-student-theme-toggle]');
            const input = document.querySelector('[data-student-theme-input]');
            const label = document.querySelector('[data-student-theme-label]');
            const preview = document.querySelector('[data-student-theme-preview]');

            function applyTheme(theme) {
                const isDark = theme === 'dark';

                if (shell) {
                    shell.classList.toggle('student-theme-dark', isDark);
                }

                if (toggle) {
                    toggle.classList.toggle('is-dark', isDark);
                    toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
                }

                if (input) {
                    input.value = isDark ? 'dark' : 'light';
                }

                if (label) {
                    label.textContent = isDark ? 'Dark' : 'Light';
                }

                if (preview) {
                    preview.textContent = isDark ? 'Dark' : 'Light';
                }

                try {
                    localStorage.setItem('studyhub-login-theme', isDark ? 'dark' : 'light');
                } catch (error) {
                    // Browser storage can be unavailable in private contexts.
                }
            }

            if (toggle && input) {
                toggle.addEventListener('click', function () {
                    applyTheme(input.value === 'dark' ? 'light' : 'dark');
                });

                applyTheme(input.value === 'dark' ? 'dark' : 'light');
            }
        });
    </script>
@endsection
