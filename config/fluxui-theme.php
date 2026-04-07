<?php

return [
    'route' => 'settings/appearance',
    'route_name' => 'appearance.edit',

    'layout' => 'components.layouts.app',

    'defaults' => [
        'accent' => 'zinc',
        'base' => 'zinc',
        'theme' => 'system',
    ],

    'appearance_resolver' => null,
];
