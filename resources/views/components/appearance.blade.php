@php
    $appearance = app(\AgenticMorf\FluxuiTheme\AppearanceService::class)->getEffective(auth()->user());
    $accent = $appearance['accent'];
    $base = $appearance['base'];
    $theme = $appearance['theme'];
@endphp
{{-- Set Flux theme from app default / user preference before @fluxAppearance runs --}}
<script>
    window.localStorage.setItem('flux.appearance', @js($theme));
</script>
{{-- Base color: redefine zinc-* to chosen base when not zinc --}}
@if ($base !== 'zinc')
<style>
    :root, .dark {
        --color-zinc-50: var(--color-{{ $base }}-50);
        --color-zinc-100: var(--color-{{ $base }}-100);
        --color-zinc-200: var(--color-{{ $base }}-200);
        --color-zinc-300: var(--color-{{ $base }}-300);
        --color-zinc-400: var(--color-{{ $base }}-400);
        --color-zinc-500: var(--color-{{ $base }}-500);
        --color-zinc-600: var(--color-{{ $base }}-600);
        --color-zinc-700: var(--color-{{ $base }}-700);
        --color-zinc-800: var(--color-{{ $base }}-800);
        --color-zinc-900: var(--color-{{ $base }}-900);
        --color-zinc-950: var(--color-{{ $base }}-950);
    }
</style>
@endif
