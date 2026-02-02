<?php

namespace RealtimeKit\TenantBroadcast\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Broadcasting\PrivateChannel channel(string $channel, mixed $tenantId = null)
 * @method static \Illuminate\Broadcasting\PresenceChannel presenceChannel(string $channel, mixed $tenantId = null)
 * @method static mixed route(string $channel, callable|string $callback)
 * 
 * @see \RealtimeKit\TenantBroadcast\TenantBroadcaster
 */
class TenantBroadcast extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tenant.broadcast';
    }
}
