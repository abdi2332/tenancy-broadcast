<?php

namespace RealtimeKit\TenantBroadcast;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Support\Str;

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

        // Get the BroadcastManager instance
        $broadcastManager = $this->app->make(BroadcastManager::class);

        // 1. Add tenantChannel macro to BroadcastManager
        BroadcastManager::macro('tenantChannel', function (string $channel, mixed $tenantId = null) {
            $prefix = config('tenant-broadcast.channel_prefix', 'tenant.{id}.');
            
            // Use explicit tenant ID or resolve from context
            $resolvedId = $tenantId ?? app(TenantResolver::class)->resolve();
            
            if (!$resolvedId && config('tenant-broadcast.strict', true)) {
                throw new \RuntimeException('Cannot resolve tenant for broadcasting.');
            }
            
            if (!$resolvedId) {
                // Non-strict mode: fallback to global channel
                $fallback = config('tenant-broadcast.fallback_prefix', 'global.');
                return new PrivateChannel($fallback . $channel);
            }
            
            $channelName = str_replace('{id}', (string) $resolvedId, $prefix) . $channel;
            return new PrivateChannel($channelName);
        });

        // 2. Add tenantPresenceChannel macro
        BroadcastManager::macro('tenantPresenceChannel', function (string $channel, mixed $tenantId = null) {
            /** @var PrivateChannel $instance */
            $instance = $this->tenantChannel($channel, $tenantId);
            return new PresenceChannel($instance->name);
        });

        // 3. FOR SERVICE BROADCASTING: Builder pattern
        BroadcastManager::macro('forTenant', function (mixed $tenantId) {
            return new class($tenantId) {
                private $tenantId;
                
                public function __construct($tenantId) {
                    $this->tenantId = $tenantId;
                }
                
                public function private(string $channel): PrivateChannel
                {
                    $prefix = config('tenant-broadcast.channel_prefix', 'tenant.{id}.');
                    $channelName = str_replace('{id}', (string) $this->tenantId, $prefix) . $channel;
                    return new PrivateChannel($channelName);
                }
                
                public function presence(string $channel): PresenceChannel
                {
                    $prefix = config('tenant-broadcast.channel_prefix', 'tenant.{id}.');
                    $channelName = str_replace('{id}', (string) $this->tenantId, $prefix) . $channel;
                    return new PresenceChannel($channelName);
                }
                
                public function channel(string $channel): string
                {
                    $prefix = config('tenant-broadcast.channel_prefix', 'tenant.{id}.');
                    return str_replace('{id}', (string) $this->tenantId, $prefix) . $channel;
                }
            };
        });

        // 4. Register tenant route authorization (still works with Broadcast facade)
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

        $this->app->singleton(TenantResolver::class);
    }
}