<?php
namespace RealtimeKit\TenantBroadcast;

use Illuminate\Support\Facades\Broadcast;

class TenantBroadcastServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tenant-broadcast.php', 'tenant-broadcast');
        $this->app->singleton(TenantChannelManager::class);
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/tenant-broadcast.php' => \config_path('tenant-broadcast.php'),
        ], 'config');

        Broadcast::channel(
            \config('tenant-broadcast.channel_prefix', 'tenant').'.{tenantId}.*', 
            TenantChannelAuth::class
        );
    }
}
