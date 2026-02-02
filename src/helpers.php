<?php

namespace RealtimeKit\TenantBroadcast;



if (! function_exists('tenant_channel')) {
    function tenant_channel(string $channel): string
    {
        return app(TenantChannelManager::class)->make($channel);
    }
}


