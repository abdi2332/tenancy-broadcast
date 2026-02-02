<?php

namespace RealtimeKit\TenantBroadcast;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use RuntimeException;

class TenantBroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! config('tenant-broadcast.enabled', true)) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/tenant-broadcast.php' => config_path('tenant-broadcast.php'),
        ], 'tenant-broadcast-config');

        // 1. Broadcast::tenantChannel() macro
        Broadcast::macro('tenantChannel', function (string $channel) {
            /** @var \Illuminate\Foundation\Auth\User|null $user */
            $user = auth()->user();
            $tenantKey = config('tenant-broadcast.tenant_key', 'tenant_id');
            
            if (! $user || ! $user->getAttribute($tenantKey)) {
                if (config('tenant-broadcast.strict', true)) {
                    throw new RuntimeException('No tenant context found for user.');
                }
                // Fallback to global if strict mode is off
                return new PrivateChannel(config('tenant-broadcast.fallback_prefix', 'global.') . $channel);
            }

            $tenantId = $user->getAttribute($tenantKey);
            $prefix = config('tenant-broadcast.channel_prefix', 'tenant.{id}.');
            
            // Replace placeholder with actual tenant ID
            $prefixed = str_replace('{id}', (string) $tenantId, $prefix) . $channel;

            return new PrivateChannel($prefixed);
        });

        // 2. Broadcast::tenantPresenceChannel()
        Broadcast::macro('tenantPresenceChannel', function (string $channel) {
            /** @var \Illuminate\Broadcasting\Channel $instance */
            $instance = Broadcast::tenantChannel($channel);
            return new PresenceChannel($instance->name);
        });

        // 3. Broadcast::tenantRoute() - Registers the channel authorization callback
        Broadcast::macro('tenantRoute', function (string $channel, callable|string $callback) {
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
        });
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/tenant-broadcast.php',
            'tenant-broadcast'
        );
    }
}