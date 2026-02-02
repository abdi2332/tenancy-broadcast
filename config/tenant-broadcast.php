<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant Key
    |--------------------------------------------------------------------------
    |
    | The attribute on your User model that contains the tenant ID.
    |
    */
    'tenant_key' => env('TENANT_BROADCAST_KEY', 'tenant_id'),

    /*
    |--------------------------------------------------------------------------
    | Channel Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix used for tenant-isolated channels.
    |
    */
    'prefix' => env('TENANT_BROADCAST_PREFIX', 'tenant'),

    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, throws exceptions if tenant cannot be resolved.
    | When disabled, falls back to non-tenant channels with warning.
    |
    */
    'strict' => env('TENANT_BROADCAST_STRICT', true),
];