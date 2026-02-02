<?php

namespace RealtimeKit\TenantBroadcast\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use RealtimeKit\TenantBroadcast\TenantChannelManager;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyTenantChannel
{
    protected TenantChannelManager $manager;

    public function __construct(TenantChannelManager $manager)
    {
        $this->manager = $manager;
    }

    public function handle(Request $request, Closure $next)
    {
        // This middleware is intended to guard broadcast auth routes
        // It can verify if the user has access to the tenant context implied by the request
        
        // For standard broadcast auth, the channel_name is usually in the request
        $channelName = $request->input('channel_name');

        if ($channelName) {
            $tenantId = $this->manager->getTenantFromChannel($channelName);

            if ($tenantId && !$this->userBelongsToTenant($request->user(), $tenantId)) {
                throw new AccessDeniedHttpException('User does not belong to this tenant.');
            }
        }

        return $next($request);
    }

    protected function userBelongsToTenant($user, $tenantId): bool
    {
        if (!$user) {
            return false;
        }

        $userTenantKey = \config('tenant-broadcast.tenant_key', 'tenant_id');
        
        return (string) $user->$userTenantKey === (string) $tenantId;
    }
}
