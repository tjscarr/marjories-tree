<?php

use TallStackUi\View\Components;

return [
    'prefix' => 'ts',
    
    'theme' => [
        'select' => 'tailwind',
    ],

    'components' => [
        'alert' => [
            'class' => Components\Alert::class,
            'alias' => 'ts-alert',
        ],
        'avatar' => [
            'class' => Components\Avatar::class,
            'alias' => 'ts-avatar',
        ],
        'badge' => [
            'class' => Components\Badge::class,
            'alias' => 'ts-badge',
        ],
        'banner' => [
            'class' => Components\Banner::class,
            'alias' => 'ts-banner',
        ],
        'button' => [
            'class' => Components\Button::class,
            'alias' => 'ts-button',
        ],
        'card' => [
            'class' => Components\Card::class,
            'alias' => 'ts-card',
        ],
        'dialog' => [
            'class' => Components\Dialog::class,
            'alias' => 'ts-dialog',
        ],
        'dropdown' => [
            'class' => Components\Dropdown::class,
            'alias' => 'ts-dropdown',
        ],
        'error' => [
            'class' => Components\Error::class,
            'alias' => 'ts-error',
        ],
        'icon' => [
            'class' => Components\Icon::class,
            'alias' => 'ts-icon',
        ],
        'modal' => [
            'class' => Components\Modal::class,
            'alias' => 'ts-modal',
        ],
        'slide' => [
            'class' => Components\Slide::class,
            'alias' => 'ts-slide',
        ],
        'tab' => [
            'class' => Components\Tab::class,
            'alias' => 'ts-tab',
        ],
        'table' => [
            'class' => Components\Table::class,
            'alias' => 'ts-table',
        ],
    ],
];
