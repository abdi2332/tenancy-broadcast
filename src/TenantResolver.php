<?php

namespace RealtimeKit\TenantBroadcast;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class TenantResolver
{
    /**
     * The explicitly set tenant ID for the current context.
     */
    protected mixed $contextTenantId = null;

    /**
     * Resolve the current tenant ID.
     * Priority: Explicit Context > Authenticated User > Fail/Null
     */
    public function resolve(): mixed
    {
        if (! is_null($this->contextTenantId)) {
            return $this->contextTenantId;
        }

        $user = $this->getAuthenticatedUser();

        if ($user) {
            $tenantKey = config('tenant-broadcast.tenant_key', 'tenant_id');
            $tenantId = $user->getAttribute($tenantKey);

            if ($tenantId) {
                return $tenantId;
            }
            
            // User exists but has no tenant ID
            if (config('tenant-broadcast.strict', true)) {
                 throw new \RuntimeException("Tenant ID column '{$tenantKey}' not found or null on authenticated user.");
            }
        }

        return $this->handleUnauthenticated();
    }

    /**
     * Set the current tenant context manually (Impersonation).
     * Useful for jobs, queues, and commands.
     */
    public function impersonate(mixed $tenantId): void
    {
        $this->contextTenantId = $tenantId;
    }

    /**
     * Clear the manual tenant context.
     */
    public function forget(): void
    {
        $this->contextTenantId = null;
    }

    protected function getAuthenticatedUser()
    {
        return Auth::user();
    }

    protected function handleUnauthenticated(): mixed
    {
        if (config('tenant-broadcast.strict', true)) {
            throw new \RuntimeException(
                'No tenant context found (User is not logged in, and no impersonation set).'
            );
        }

        return null;
    }
}