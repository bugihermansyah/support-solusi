<?php

return [
    'navigation' => [
        'token' => [
            'cluster' => null,
            'group' => 'Utility',
            'sort' => 99,
            'icon' => 'heroicon-o-key',
            // 'should_register_navigation' => true,
        ],
    ],
    'models' => [
        'token' => [
            'enable_policy' => false,
        ],
    ],
    'route' => [
        'panel_prefix' => false,
        'use_resource_middlewares' => false,
    ],
    'tenancy' => [
        'enabled' => false,
        'awareness' => false,
    ],
    'login-rules' => [
        'email' => 'required|email',
        'password' => 'required',
    ],
    'use-spatie-permission-middleware' => false,
];
