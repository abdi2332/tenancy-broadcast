# Laravel Tenant Broadcast

A lightweight, opinionated package for securing real-time broadcasting in shared-database multi-tenant applications.

This package automatically scopes broadcast channels by tenant ID and enforces strict authorization checks, preventing cross-tenant data leaks in Laravel Reverb, Pusher, or other broadcasting drivers. It is designed specifically for applications where tenancy is determined by a column on the `users` table (e.g., `tenant_id`).

## Features

- **Automatic Channel Scoping**: Automatically converts logical channel names (e.g., `orders`) into tenant-scoped channels (e.g., `tenant.1.orders`).
- **Strict Authorization**: Enforces tenant access controls at the routing level, ensuring users can only subscribe to channels belonging to their tenant.
- **Background Broadcasting**: Supports broadcasting from background jobs, queues, and system commands where no user is authenticated.
- **Zero Heavy Dependencies**: Does not require complex multi-tenancy frameworks.

## Requirements

- PHP 8.1+
- Laravel 10.0+ or 11.0+

## Installation

Install the package via Composer:

```bash
composer require realtime-kit/tenant-broadcast
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=tenant-broadcast-config
```

## Configuration

Configure the package in `config/tenant-broadcast.php` or via your `.env` file.

```dotenv
# Enable or disable the package
TENANT_BROADCAST_ENABLED=true

# The column on your User model that stores the tenant ID
TENANT_BROADCAST_KEY=tenant_id

# The prefix pattern for tenant-scoped channels (use {id} as placeholder)
TENANT_BROADCAST_PREFIX=tenant.{id}.

# Strict mode: throw exceptions if tenant context cannot be resolved
TENANT_BROADCAST_STRICT=true
```

## Usage

### 1. Broadcasting Events

In your Event classes, use the `TenantBroadcast` facade instead of `PrivateChannel`.

```php
use RealtimeKit\TenantBroadcast\Facades\TenantBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class OrderUpdated implements ShouldBroadcast
{
    public function __construct(
        public Order $order
    ) {}

    public function broadcastOn(): array
    {
        // Automatically scopes to: private-tenant.{tenant_id}.orders
        return [
            TenantBroadcast::channel('orders')
        ];
    }
}
```

### 2. Authorizing Channels

Register your channel routes in `routes/channels.php` using the `TenantBroadcast::route` method. This automatically applies the tenant authorization guard before your callback runs.

```php
use RealtimeKit\TenantBroadcast\Facades\TenantBroadcast;

// registers: tenant.{tenant_id}.orders
TenantBroadcast::route('orders', function ($user, $orderId) {
    // This callback ONLY executes if $user->tenant_id matches the channel's tenant ID.
    // You can now safely focus on resource authorization.
    return $user->can('view', Order::find($orderId));
});
```

### 3. Client-Side (Laravel Echo)

Your frontend application must subscribe to the full, tenant-scoped channel name.

```javascript
// Assuming you pass the user's tenant ID to your frontend
const tenantId = user.tenant_id;

Echo.private(`tenant.${tenantId}.orders`)
    .listen('OrderUpdated', (e) => {
        console.log(e.order);
    });
```

## Advanced Usage

### Background Jobs & Queues

When broadcasting from a Job or Queue, there is no authenticated user. You must provide the tenant context explicitly.

**Option 1: Explicit Tenant ID**

Pass the tenant ID as the second argument to `channel`.

```php
public function handle()
{
    $order = Order::find($this->orderId);

    broadcast(new OrderUpdated($order))->to(
        TenantBroadcast::channel('orders', $order->tenant_id)
    );
}
```

**Option 2: Context Impersonation**

If you need to broadcast multiple events or use existing logic that relies on implicit resolution, you can "impersonate" a tenant context.

```php
use RealtimeKit\TenantBroadcast\TenantResolver;

public function handle(TenantResolver $resolver)
{
    $resolver->impersonate($this->tenantId);

    // This will now use the impersonated tenant ID automatically
    // broadcast(new OrderUpdated($this->order));

    $resolver->forget();
}
```

## Security

This package operates in **Strict Mode** by default.

If `TenantBroadcast::channel()` is called without an authenticated user and without an explicit tenant ID (or impersonation context), it will throw a `RuntimeException`. This prevents accidental broadcasting to global or undefined channels.

## License

The MIT License (MIT). Please see License File for more information.
