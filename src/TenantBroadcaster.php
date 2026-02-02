<?php

namespace RealtimeKit\TenantBroadcast;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Support\Facades\Broadcast;

class TenantBroadcaster
{
    protected TenantResolver $resolver;

    public function __construct(TenantResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Create a tenant-scoped PrivateChannel.
     */
    public function channel(string $channel, mixed $tenantId = null): PrivateChannel
    {
        // Resolve: Explicit Arg > Context > Auth User
        $resolvedId = $tenantId ?? $this->resolver->resolve();

        $prefix = config('tenant-broadcast.channel_prefix', 'tenant.{id}.');
        
        // Replace placeholder with actual tenant ID
        $prefixed = str_replace('{id}', (string) $resolvedId, $prefix) . $channel;

        return new PrivateChannel($prefixed);
    }

    /**
     * Create a tenant-scoped PresenceChannel.
     */
    public function presenceChannel(string $channel, mixed $tenantId = null): PresenceChannel
    {
        $instance = $this->channel($channel, $tenantId);
        return new PresenceChannel($instance->name);
    }

    /**
     * Register a tenant-scoped channel authorization route.
     * Equivalent to Broadcast::channel() but with tenant guards.
     */
    public function route(string $channel, callable|string $callback)
    {
        $prefix = config('tenant-broadcast.channel_prefix', 'tenant.{id}.');
        $patternVal = str_replace('{id}', '{tenant_id}', $prefix) . $channel;

        return Broadcast::channel($patternVal, function ($user, $tenantId, ...$args) use ($callback) {
            // 1. Strict Tenant Check
            $userTenantId = $user->getAttribute(config('tenant-broadcast.tenant_key', 'tenant_id'));
            
            // Compare as strings to be safe
            if ((string) $userTenantId !== (string) $tenantId) {
                return false;
            }

            // 2. Execute original callback
            if (is_string($callback)) {
                // Handle class based channel classes
                return app($callback)->join($user, ...$args);
            }
            
            return $callback($user, ...$args);
        });
    }
}
