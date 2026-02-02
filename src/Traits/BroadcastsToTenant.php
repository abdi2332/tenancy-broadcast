<?php

namespace RealtimeKit\TenantBroadcast\Traits;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Broadcast;

trait BroadcastsToTenant
{
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Automatically broadcast to the tenant channel
        // e.g., 'orders' -> 'tenant.1.orders'
        // This assumes the class using this trait has a 'broadcastChannel' method or property
        // or we just default to the class name or a standard convention.
        
        // However, usually this trait is used on Events.
        // If the user defines broadcastOn, they should use Broadcast::tenantChannel().
        
        // This trait might be better as a helper to return the correct channel instance.
        
        return Broadcast::tenantChannel($this->broadcastChannel());
    }

    /**
     * Get the channel name for the event.
     * Can be overridden by the consuming class.
     */
    protected function broadcastChannel(): string
    {
        return property_exists($this, 'channel') ? $this->channel : '';
    }
}