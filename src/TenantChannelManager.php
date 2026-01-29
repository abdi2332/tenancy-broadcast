<?php
namespace RealtimeKit\TenantBroadcast;

class TenantChannelManager
{
    protected string $prefix;

    public function __construct()
    {
        $this->prefix = \config('tenant-broadcast.channel_prefix', 'tenant');
    }

    public function channel(string $name, int|string $tenantId): string
    {
        return "{$this->prefix}.{$tenantId}.{$name}";
    }

    public function getTenantFromChannel(string $channel): ?string
    {
        $parts = explode('.', $channel);
        return $parts[1] ?? null;
    }
}
