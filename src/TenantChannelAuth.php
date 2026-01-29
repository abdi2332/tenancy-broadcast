<?php
namespace RealtimeKit\TenantBroadcast;

use Illuminate\Support\Facades\Auth;

class TenantChannelAuth
{
    public function join($user, $tenantId)
    {
        $userTenantId = $user->{\config('tenant-broadcast.tenant_key', 'tenant_id')};
        
        return (string) $userTenantId === (string) $tenantId;
    }
}
