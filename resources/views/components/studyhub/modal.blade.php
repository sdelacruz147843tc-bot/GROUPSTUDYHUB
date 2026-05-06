@props([
    'title',
    'subtitle' => null,
    'closeData' => null,
    'open' => false,
    'size' => 'md',
])

@php
    $panelSize = [
        'sm' => 'max-w-xl',
        'md' => 'max-w-2xl',
        'lg' => 'max-w-3xl',
        'xl' => 'max-w-4xl',
    ][$size] ?? 'max-w-2xl';
@endphp

<div
    {{ $attributes->merge([
        'class' => 'fixed inset-0 z-40 hidden items-center justify-center p-4 sm:p-7 [&.is-open]:flex' . ($open ? ' is-open' : ''),
        'data-studyhub-modal' => '',
    ]) }}
>
    <button
        class="absolute inset-0 cursor-pointer border-0 bg-[#0f1611]/50 backdrop-blur-md"
        type="button"
        aria-label="Close modal"
        @if ($closeData) {!! $closeData !!} @endif
    ></button>

    <div class="relative z-[1] max-h-[calc(100vh-44px)] w-full {{ $panelSize }} overflow-auto rounded-[26px] border border-emerald-100/90 bg-white shadow-[0_30px_72px_rgba(17,31,24,0.24)]">
        <div class="flex items-start justify-between gap-4 border-b border-emerald-100 bg-emerald-100/70 px-5 py-5 sm:px-6">
            <div class="grid gap-2">
                @isset($kicker)
                    <div>{{ $kicker }}</div>
                @endisset

                <div>
                    <h3 class="m-0 text-[2rem] font-black leading-none tracking-[-0.04em] text-[#183425]">{{ $title }}</h3>
                    @if ($subtitle)
                        <p class="m-0 mt-2 text-sm font-semibold text-[#5f776b]">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>

            <button
                class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-emerald-100 bg-white/70 text-2xl leading-none text-[#183425] transition hover:bg-white"
                type="button"
                aria-label="Close modal"
                @if ($closeData) {!! $closeData !!} @endif
            >&times;</button>
        </div>

        <div class="px-5 py-5 sm:px-6">
            {{ $slot }}
        </div>
    </div>
</div>
