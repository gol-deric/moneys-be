<?php

return [
    'free' => [
        'max_subscriptions' => 3,
        'notification_days_before' => [1], // Fixed: 1 day before
        'can_customize_notifications' => false,
        'can_export' => false,
        'can_view_reports' => false,
        'history_days' => 30,
    ],

    'pro' => [
        'max_subscriptions' => null, // unlimited
        'notification_days_before' => null, // customizable by user
        'can_customize_notifications' => true,
        'can_export' => true,
        'can_view_reports' => true,
        'history_days' => null, // unlimited
        'price_yearly' => 10.00,
        'currency' => 'USD',
    ],
];
