<?php
namespace RealtimeKit\TenantBroadcast\Traits;

use RealtimeKit\TenantBroadcast\TenantChannelManager;
use Illuminate\Support\Facades\Broadcast;

trait BroadcastsToTenant
{
    public function broadcastToTenant(string $eventClass, mixed $payload, int|string $tenantId)
    {
        $channel = app(TenantChannelManager::class)->channel(
            str_replace('\\', '.', $eventClass),
            $tenantId
        );

        broadcast(new $eventClass($payload))->toOthers();
    }
}
