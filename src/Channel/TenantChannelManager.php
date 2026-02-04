<?php

namespace RealtimeKit\TenantBroadcast;

use RealtimeKit\TenantBroadcast\TenantResolver;
use Illuminate\Broadcasting\Channel;

class TenantChannelManager
{
    public function __construct(
        protected TenantResolver $resolver
    ) {}

    public function make(string $channel): string
    {
        $tenantId = $this->resolver->resolve();

        if (! $tenantId) {

            return $channel;
        }

        $prefix = config('tenant-broadcast.prefix', 'tenant');

        return "{$prefix}.{$tenantId}.{$channel}";
    }

    public function wrap(Channel $channel): Channel
    {
        $name = $this->make($channel->name);

        return new $channel($name, $channel->attributes);
    }
}