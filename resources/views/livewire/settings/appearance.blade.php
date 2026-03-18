<?php

use AgenticMorf\FluxuiTheme\AppearanceService;
use Livewire\Volt\Component;

new class extends Component {
    public string $theme;

    public string $accent;

    public string $base;

    public function mount(AppearanceService $appearance): void
    {
        $effective = $appearance->getEffective(auth()->user());
        $prefs = auth()->user()->appearance_preferences ?? [];
        $this->theme = $effective['theme'];
        $this->accent = $prefs['accent'] ?? '';
        $this->base = $prefs['base'] ?? '';
    }

    public function updatedTheme(string $value): void
    {
        $this->persistPreference('theme', $value);
    }

    public function updatedAccent(?string $value): void
    {
        $this->persistPreference('accent', $value ?? '');
    }

    public function updatedBase(?string $value): void
    {
        $this->persistPreference('base', $value ?? '');
    }

    private function persistPreference(string $key, string $value): void
    {
        if (! auth()->check()) {
            return;
        }

        $prefs = auth()->user()->appearance_preferences ?? [];
        if ($value === '') {
            unset($prefs[$key]);
        } else {
            $prefs[$key] = $value;
        }
        auth()->user()->update(['appearance_preferences' => $prefs]);
    }
}; ?>

@php
    $appearanceService = app(AppearanceService::class);
    // Target #flux-accent so we override the wrapper's server-rendered classes for live updates
    $accentCss = [
        'zinc' => '#flux-accent{--color-accent:var(--color-zinc-800);--color-accent-content:var(--color-zinc-800);--color-accent-foreground:var(--color-white)}.dark #flux-accent{--color-accent:var(--color-white);--color-accent-content:var(--color-white);--color-accent-foreground:var(--color-zinc-800)}',
        'red' => '#flux-accent{--color-accent:var(--color-red-500);--color-accent-content:var(--color-red-600);--color-accent-foreground:var(--color-white)}.dark #flux-accent{--color-accent:var(--color-red-500);--color-accent-content:var(--color-red-400);--color-accent-foreground:var(--color-white)}',
        'orange' => '#flux-accent{--color-accent:var(--color-orange-500);--color-accent-content:var(--color-orange-600);--color-accent-foreground:var(--color-white)}.dark #flux-accent{--color-accent:var(--color-orange-400);--color-accent-content:var(--color-orange-400);--color-accent-foreground:var(--color-orange-950)}',
        'amber' => '#flux-accent{--color-accent:var(--color-amber-400);--color-accent-content:var(--color-amber-600);--color-accent-foreground:var(--color-amber-950)}.dark #flux-accent{--color-accent:var(--color-amber-400);--color-accent-content:var(--color-amber-400);--color-accent-foreground:var(--color-amber-950)}',
        'yellow' => '#flux-accent{--color-accent:var(--color-yellow-400);--color-accent-content:var(--color-yellow-600);--color-accent-foreground:var(--color-yellow-950)}.dark #flux-accent{--color-accent:var(--color-yellow-400);--color-accent-content:var(--color-yellow-400);--color-accent-foreground:var(--color-yellow-950)}',
        'lime' => '#flux-accent{--color-accent:var(--color-lime-400);--color-accent-content:var(--color-lime-600);--color-accent-foreground:var(--color-lime-900)}.dark #flux-accent{--color-accent:var(--color-lime-400);--color-accent-content:var(--color-lime-400);--color-accent-foreground:var(--color-lime-950)}',
        'green' => '#flux-accent{--color-accent:var(--color-green-600);--color-accent-content:var(--color-green-600);--color-accent-foreground:var(--color-white)}.dark #flux-accent{--color-accent:var(--color-green-600);--color-accent-content:var(--color-green-400);--color-accent-foreground:var(--color-white)}',
        'emerald' => '#flux-accent{--color-accent:var(--color-emerald-600);--color-accent-content:var(--color-emerald-600);--color-accent-foreground:var(--color-white)}.dark #flux-accent{--color-accent:var(--color-emerald-600);--color-accent-content:var(--color-emerald-400);--color-accent-foreground:var(--color-white)}',
        'teal' => '#flux-accent{--color-accent:var(--color-teal-600);--color-accent-content:var(--color-teal-600);--color-accent-foreground:var(--color-white)}.dark #flux-accent{--color-accent:var(--color-teal-600);--color-accent-content:var(--color-teal-400);--color-accent-foreground:var(--color-white)}',
        'cyan' => '#flux-accent{--color-accent:var(--color-cyan-600);--color-accent-content:var(--color-cyan-600);--color-accent-foreground:var(--color-white)}.dark #flux-accent{--color-accent:var(--color-cyan-600);--color-accent-content:var(--color-cyan-400);--color-accent-foreground:var(--color-white)}',
        'sky' => '#flux-accent{--color-accent:var(--color-sky-600);--color-accent-content:var(--color-sky-600);--color-accent-foreground:var(--color-white)}.dark #flux-accent{--color-accent:var(--color-sky-600);--color-accent-content:var(--color-sky-400);--color-accent-foreground:var(--color-white)}',
        'blue' => '#flux-accent{--color-accent:var(--color-blue-500);--color-accent-content:var(--color-blue-600);--color-accent-foreground:var(--color-white)}.dark #flux-accent{--color-accent:var(--color-blue-500);--color-accent-content:var(--color-blue-400);--color-accent-foreground:var(--color-white)}',
        'indigo' => '#flux-accent{--color-accent:var(--color-indigo-500);--color-accent-content:var(--color-indigo-600);--color-accent-foreground:var(--color-white)}.dark #flux-accent{--color-accent:var(--color-indigo-500);--color-accent-content:var(--color-indigo-300);--color-accent-foreground:var(--color-white)}',
        'violet' => '#flux-accent{--color-accent:var(--color-violet-500);--color-accent-content:var(--color-violet-600);--color-accent-foreground:var(--color-white)}.dark #flux-accent{--color-accent:var(--color-violet-500);--color-accent-content:var(--color-violet-400);--color-accent-foreground:var(--color-white)}',
        'purple' => '#flux-accent{--color-accent:var(--color-purple-500);--color-accent-content:var(--color-purple-600);--color-accent-foreground:var(--color-white)}.dark #flux-accent{--color-accent:var(--color-purple-500);--color-accent-content:var(--color-purple-300);--color-accent-foreground:var(--color-white)}',
        'fuchsia' => '#flux-accent{--color-accent:var(--color-fuchsia-600);--color-accent-content:var(--color-fuchsia-600);--color-accent-foreground:var(--color-white)}.dark #flux-accent{--color-accent:var(--color-fuchsia-600);--color-accent-content:var(--color-fuchsia-400);--color-accent-foreground:var(--color-white)}',
        'pink' => '#flux-accent{--color-accent:var(--color-pink-600);--color-accent-content:var(--color-pink-600);--color-accent-foreground:var(--color-white)}.dark #flux-accent{--color-accent:var(--color-pink-600);--color-accent-content:var(--color-pink-400);--color-accent-foreground:var(--color-white)}',
        'rose' => '#flux-accent{--color-accent:var(--color-rose-500);--color-accent-content:var(--color-rose-500);--color-accent-foreground:var(--color-white)}.dark #flux-accent{--color-accent:var(--color-rose-500);--color-accent-content:var(--color-rose-400);--color-accent-foreground:var(--color-white)}',
    ];
@endphp
<section
    class="w-full"
    x-data="{
        accentCss: @js($accentCss),
        applyStyle(accent, base) {
            accent = accent || 'zinc';
            base = base || 'zinc';
            let styleEl = document.getElementById('fluxui-theme-live');
            if (!styleEl) {
                styleEl = document.createElement('style');
                styleEl.id = 'fluxui-theme-live';
                document.head.appendChild(styleEl);
            }
            let css = this.accentCss[accent] || this.accentCss.zinc;
            if (base !== 'zinc') {
                css += ':root,.dark{';
                ['50','100','200','300','400','500','600','700','800','900','950'].forEach(s => {
                    css += `--color-zinc-${s}:var(--color-${base}-${s});`;
                });
                css += '}';
            }
            styleEl.textContent = css;
        }
    }"
    x-init="$watch('$flux.appearance', value => { if ($wire.theme !== value) $wire.set('theme', value) })"
    x-effect="applyStyle($wire.accent, $wire.base)"
>
    @includeIf('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
        <flux:field>
            <flux:label>{{ __('Theme') }}</flux:label>
            <flux:radio.group variant="segmented" wire:model.live="theme" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
            </flux:radio.group>
        </flux:field>

        <flux:field class="mt-4">
            <flux:label>{{ __('Accent color') }}</flux:label>
            <flux:description>{{ __('Primary buttons, links, and highlights') }}</flux:description>
            <div class="mt-2 grid grid-cols-3 gap-2" role="radiogroup" aria-label="{{ __('Accent color') }}">
                <button
                    type="button"
                    wire:click="$set('accent', '')"
                    @click="applyStyle('', $wire.base)"
                    class="flex items-center gap-1.5 rounded-md border-2 px-2 py-1.5 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 {{ $accent === '' ? 'border-zinc-900 dark:border-white ring-2 ring-zinc-400 dark:ring-zinc-500' : 'border-zinc-200 dark:border-zinc-600 hover:border-zinc-300 dark:hover:border-zinc-500' }}"
                    title="{{ __('App default') }}"
                >
                    <span class="block size-5 shrink-0 rounded-sm bg-gradient-to-br from-zinc-400 to-zinc-600 dark:from-zinc-500 dark:to-zinc-700"></span>
                    <span class="truncate text-xs text-zinc-600 dark:text-zinc-400">{{ __('App default') }}</span>
                </button>
                @foreach(AppearanceService::ACCENT_COLORS as $value => $label)
                    <button
                        type="button"
                        wire:click="$set('accent', '{{ $value }}')"
                        @click="applyStyle('{{ $value }}', $wire.base)"
                        class="flex items-center gap-1.5 rounded-md border-2 px-2 py-1.5 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 {{ $accent === $value ? 'border-zinc-900 dark:border-white ring-2 ring-zinc-400 dark:ring-zinc-500' : 'border-zinc-200 dark:border-zinc-600 hover:border-zinc-300 dark:hover:border-zinc-500' }}"
                        title="{{ $label }}"
                    >
                        <span class="block size-5 shrink-0 rounded-sm {{ $appearanceService->getAccentSwatchClass($value) }}"></span>
                        <span class="truncate text-xs text-zinc-600 dark:text-zinc-400">{{ $label }}</span>
                    </button>
                @endforeach
            </div>
        </flux:field>

        <flux:field class="mt-4">
            <flux:label>{{ __('Base color') }}</flux:label>
            <flux:description>{{ __('Backgrounds, text, and surfaces') }}</flux:description>
            <div class="mt-2 grid grid-cols-3 gap-2" role="radiogroup" aria-label="{{ __('Base color') }}">
                <button
                    type="button"
                    wire:click="$set('base', '')"
                    @click="applyStyle($wire.accent, '')"
                    class="flex items-center gap-1.5 rounded-md border-2 px-2 py-1.5 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 {{ $base === '' ? 'border-zinc-900 dark:border-white ring-2 ring-zinc-400 dark:ring-zinc-500' : 'border-zinc-200 dark:border-zinc-600 hover:border-zinc-300 dark:hover:border-zinc-500' }}"
                    title="{{ __('App default') }}"
                >
                    <span class="block size-5 shrink-0 rounded-sm bg-gradient-to-br from-zinc-400 to-zinc-600 dark:from-zinc-500 dark:to-zinc-700"></span>
                    <span class="truncate text-xs text-zinc-600 dark:text-zinc-400">{{ __('App default') }}</span>
                </button>
                @foreach(array_keys(AppearanceService::BASE_COLORS) as $value)
                    <button
                        type="button"
                        wire:click="$set('base', '{{ $value }}')"
                        @click="applyStyle($wire.accent, '{{ $value }}')"
                        class="flex items-center gap-1.5 rounded-md border-2 px-2 py-1.5 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 {{ $base === $value ? 'border-zinc-900 dark:border-white ring-2 ring-zinc-400 dark:ring-zinc-500' : 'border-zinc-200 dark:border-zinc-600 hover:border-zinc-300 dark:hover:border-zinc-500' }}"
                        title="{{ AppearanceService::BASE_COLORS[$value] }}"
                    >
                        <span class="block size-5 shrink-0 rounded-sm {{ $appearanceService->getBaseSwatchClass($value) }}"></span>
                        <span class="truncate text-xs text-zinc-600 dark:text-zinc-400">{{ AppearanceService::BASE_COLORS[$value] }}</span>
                    </button>
                @endforeach
            </div>
        </flux:field>
    </x-settings.layout>
</section>
