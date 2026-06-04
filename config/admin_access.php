<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin titles (role = admin only)
    |--------------------------------------------------------------------------
    |
    | ceo                  — C.E.O / executive oversight
    | manager              — Operations manager (day-to-day platform oversight)
    | system_administrator — Technical system administrator
    |
    */
    'titles' => [
        'ceo' => [
            'label' => 'C.E.O',
            'short' => 'CEO',
            'description' => 'Executive oversight — strategy, finance, analytics, and key approvals.',
            'badge' => 'primary',
        ],
        'manager' => [
            'label' => 'Operations Manager',
            'short' => 'Manager',
            'description' => 'Day-to-day operations — users, orders, logistics, verifications, and disputes.',
            'badge' => 'success',
        ],
        'system_administrator' => [
            'label' => 'Admin Dashboard',
            'short' => 'Admin',
            'description' => 'Overview of your platform statistics and management.',
            'badge' => 'dark',
        ],
    ],

    'permissions' => [
        'dashboard' => ['ceo', 'manager', 'system_administrator'],

        'users.view' => ['ceo', 'manager', 'system_administrator'],
        'users.create' => ['manager', 'system_administrator'],
        'users.export' => ['ceo', 'manager', 'system_administrator'],
        'users.manage' => ['manager', 'system_administrator'],
        'users.approve' => ['ceo', 'manager', 'system_administrator'],
        'users.create_admin' => ['system_administrator'],
        'users.impersonate' => ['system_administrator'],

        'verifications' => ['ceo', 'manager', 'system_administrator'],
        'orders' => ['ceo', 'manager', 'system_administrator'],
        'meals' => ['manager', 'system_administrator'],
        'finance' => ['ceo', 'system_administrator'],
        'finance.refund' => ['system_administrator'],
        'invoices' => ['ceo', 'manager', 'system_administrator'],
        'logistics' => ['manager', 'system_administrator'],
        'disputes' => ['ceo', 'manager', 'system_administrator'],
        'notifications' => ['manager', 'system_administrator'],
        'analytics' => ['ceo', 'system_administrator'],
        'config' => ['system_administrator'],
        'zones' => ['system_administrator'],
        'system.monitor' => ['system_administrator'],
        'system.security' => ['system_administrator'],
        'system.backups' => ['system_administrator'],
    ],
];
