<?php

namespace AgenticMorf\FluxuiTheme;

class AppearanceService
{
    public const ACCENT_COLORS = [
        'zinc' => 'Base (default)',
        'red' => 'Red',
        'orange' => 'Orange',
        'amber' => 'Amber',
        'yellow' => 'Yellow',
        'lime' => 'Lime',
        'green' => 'Green',
        'emerald' => 'Emerald',
        'teal' => 'Teal',
        'cyan' => 'Cyan',
        'sky' => 'Sky',
        'blue' => 'Blue',
        'indigo' => 'Indigo',
        'violet' => 'Violet',
        'purple' => 'Purple',
        'fuchsia' => 'Fuchsia',
        'pink' => 'Pink',
        'rose' => 'Rose',
    ];

    public const BASE_COLORS = [
        'slate' => 'Slate',
        'gray' => 'Gray',
        'zinc' => 'Zinc',
        'neutral' => 'Neutral',
        'stone' => 'Stone',
        'mauve' => 'Mauve',
        'olive' => 'Olive',
        'mist' => 'Mist',
        'taupe' => 'Taupe',
    ];

    public const THEMES = [
        'system' => 'System',
        'light' => 'Light',
        'dark' => 'Dark',
    ];

    public function __construct(
        protected array $defaults = []
    ) {
        $this->defaults = array_merge([
            'accent' => 'zinc',
            'base' => 'zinc',
            'theme' => 'system',
        ], $defaults);
    }

    public function getEffective(?object $user = null): array
    {
        $prefs = $user?->appearance_preferences ?? [];

        return [
            'accent' => $prefs['accent'] ?? $this->defaults['accent'],
            'base' => $prefs['base'] ?? $this->defaults['base'],
            'theme' => $prefs['theme'] ?? $this->defaults['theme'],
        ];
    }

    /**
     * Swatch utilities must match class names Tailwind actually generates. Dynamic
     * values from PHP are invisible to Tailwind’s scanner; use the same light/dark
     * pairs as Flux’s avatar badge map so stubs already in @source produce CSS.
     */
    public function getAccentSwatchClass(string $color): string
    {
        return match ($color) {
            'zinc' => 'bg-zinc-600 dark:bg-zinc-400',
            'red' => 'bg-red-500 dark:bg-red-400',
            'orange' => 'bg-orange-500 dark:bg-orange-400',
            'amber' => 'bg-amber-500 dark:bg-amber-400',
            'yellow' => 'bg-yellow-500 dark:bg-yellow-400',
            'lime' => 'bg-lime-500 dark:bg-lime-400',
            'green' => 'bg-green-500 dark:bg-green-400',
            'emerald' => 'bg-emerald-500 dark:bg-emerald-400',
            'teal' => 'bg-teal-500 dark:bg-teal-400',
            'cyan' => 'bg-cyan-500 dark:bg-cyan-400',
            'sky' => 'bg-sky-500 dark:bg-sky-400',
            'blue' => 'bg-blue-500 dark:bg-blue-400',
            'indigo' => 'bg-indigo-500 dark:bg-indigo-400',
            'violet' => 'bg-violet-500 dark:bg-violet-400',
            'purple' => 'bg-purple-500 dark:bg-purple-400',
            'fuchsia' => 'bg-fuchsia-500 dark:bg-fuchsia-400',
            'pink' => 'bg-pink-500 dark:bg-pink-400',
            'rose' => 'bg-rose-500 dark:bg-rose-400',
            default => 'bg-zinc-600 dark:bg-zinc-400',
        };
    }

    public function getBaseSwatchClass(string $color): string
    {
        return match ($color) {
            'slate' => 'bg-slate-500',
            'gray' => 'bg-gray-500',
            'zinc' => 'bg-zinc-500',
            'neutral' => 'bg-neutral-500',
            'stone' => 'bg-stone-500',
            'mauve' => 'bg-mauve-500',
            'olive' => 'bg-olive-500',
            'mist' => 'bg-mist-500',
            'taupe' => 'bg-taupe-500',
            default => 'bg-zinc-500',
        };
    }
}
