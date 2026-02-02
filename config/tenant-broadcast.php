<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enable Package
    |--------------------------------------------------------------------------
    |
    | Enable or disable the tenant broadcast package.
    |
    */
    'enabled' => env('TENANT_BROADCAST_ENABLED', true),

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
    | The prefix pattern for tenant-isolated channels.
    | Use {id} as a placeholder for the tenant ID.
    |
    */
    'channel_prefix' => env('TENANT_BROADCAST_PREFIX', 'tenant.{id}.'),

    /*
    |--------------------------------------------------------------------------
    | Strict Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, throws exceptions if tenant cannot be resolved.
    | When disabled, falls back to null (not recommended for production).
    |
    */
    'strict' => env('TENANT_BROADCAST_STRICT', true),
];