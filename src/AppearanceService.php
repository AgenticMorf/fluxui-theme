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

    public function getAccentSwatchClass(string $color): string
    {
        return match ($color) {
            'zinc' => 'bg-zinc-600 dark:bg-zinc-400',
            'red' => 'bg-red-500',
            'orange' => 'bg-orange-500',
            'amber' => 'bg-amber-400',
            'yellow' => 'bg-yellow-400',
            'lime' => 'bg-lime-400',
            'green' => 'bg-green-600',
            'emerald' => 'bg-emerald-600',
            'teal' => 'bg-teal-600',
            'cyan' => 'bg-cyan-600',
            'sky' => 'bg-sky-600',
            'blue' => 'bg-blue-500',
            'indigo' => 'bg-indigo-500',
            'violet' => 'bg-violet-500',
            'purple' => 'bg-purple-500',
            'fuchsia' => 'bg-fuchsia-600',
            'pink' => 'bg-pink-600',
            'rose' => 'bg-rose-500',
            default => 'bg-zinc-600',
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
