<?php

return [
    'users' => [
        'login_relations' => ['roleDefinition', 'totpData'],
        'pending_relations' => ['roleDefinition', 'totpData'],
    ],

    'routes' => [
        'login' => 'login',
        'dashboard' => 'dashboard',
        'profile' => 'profile.edit',
        'two_factor_challenge' => 'two-factor.challenge',
        'two_factor_cancel' => 'two-factor.challenge.cancel',
        'two_factor_setup' => 'two-factor.setup',
    ],

    'views' => [
        'guest_layout_component' => 'guest-layout',
        'security_layout_component' => 'security-layout',
    ],
];
