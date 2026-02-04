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


    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/tenant-broadcast.php',
            'tenant-broadcast'
        );

        $this->app->singleton(\RealtimeKit\TenantBroadcast\TenantResolver::class);
        
        $this->app->singleton('tenant.broadcast', function ($app) {
            return new \RealtimeKit\TenantBroadcast\TenantBroadcaster(
                $app->make(\RealtimeKit\TenantBroadcast\TenantResolver::class)
            );
        });
    }
}