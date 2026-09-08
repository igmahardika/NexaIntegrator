<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MikroTik RouterOS API Configuration
    |--------------------------------------------------------------------------
    | Default settings for RouterOS API connections.
    | Individual router credentials are stored in the locations table.
    */

    'default_port'    => env('MIKROTIK_DEFAULT_PORT', 8728),
    'timeout'         => env('MIKROTIK_TIMEOUT', 5),
    'attempts'        => env('MIKROTIK_ATTEMPTS', 3),
    'delay'           => 1,
    'ssl'             => false,
    'ssl_port'        => 8729,

    /*
    |--------------------------------------------------------------------------
    | Hotspot Profile Defaults
    |--------------------------------------------------------------------------
    */
    'default_profile'   => 'hspotcp',
    'survey_profile'    => 'survey-user',
    'voucher_profile'   => 'voucher-user',
    'member_profile'    => 'member-user',

    /*
    |--------------------------------------------------------------------------
    | Session Settings
    |--------------------------------------------------------------------------
    */
    'survey_session_minutes'  => 60 * 8,   // 8 hours
    'voucher_mac_per_code'    => 1,         // 1 device per voucher
];
