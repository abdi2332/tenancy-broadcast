<?php

namespace RealtimeKit\TenantBroadcast;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;


class TenantResolver
{
    public function resolve(): mixed
    {
        $user = $this->getAuthenticatedUser();

        if (! $user) {
            return $this->handleUnauthenticated();
        }

        $tenantKey = config('tenant-broadcast.tenant_key');
        $tenantId = $user->{$tenantKey};

        if (! $tenantId && config('tenant-broadcast.strict')) {
            throw new \RuntimeException(
                "Tenant ID not found on user. Check config 'tenant_key' or disable strict mode."
            );
        }

        return $tenantId;
    }

    protected function getAuthenticatedUser(): ?Authenticatable
    {
        return Auth::user();
    }

    protected function handleUnauthenticated(): mixed
    {
        if (config('tenant-broadcast.strict')) {
            throw new \RuntimeException(
                'No authenticated user for tenant resolution.'
            );
        }

        return null;
    }
}