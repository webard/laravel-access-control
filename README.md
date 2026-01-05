# Laravel Access Control

![Laravel Access Control](https://banners.beyondco.de/Laravel%20Access%20Control.png?theme=dark&packageManager=composer+require&packageName=webard%2Flaravel-access-control&pattern=wiggle&style=style_1&description=Control+access+using+Enums+and+Permission+Voters&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg#gh-dark-mode-only)

![Laravel Access Control](https://banners.beyondco.de/Laravel%20Access%20Control.png?theme=light&packageManager=composer+require&packageName=webard%2Flaravel-access-control&pattern=wiggle&style=style_1&description=Control+access+using+Enums+and+Permission+Voters&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg#gh-light-mode-only)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/webard/laravel-access-control.svg?style=flat-square)](https://packagist.org/packages/webard/laravel-access-control)
[![Total Downloads](https://img.shields.io/packagist/dt/webard/laravel-access-control.svg?style=flat-square)](https://packagist.org/packages/webard/laravel-access-control)

A modular access control library for Laravel applications that uses **enum-based permissions** and a **voter system**. Perfect for modular monolith architectures where different modules can define their own permission logic.

## Key Features

- 🔐 **Enum-based permissions** - Define permissions as PHP enums for type-safety and IDE autocomplete
- 🗳️ **Voter system** - Replace Laravel Policies with flexible voters that can be registered from any module
- 📦 **Modular architecture** - Each module can register its own voters without modifying core logic
- 🏷️ **Permission metadata** - Add names, descriptions, and groups to permissions via PHP attributes
- ⚡ **Laravel Gate integration** - Works seamlessly with Laravel's authorization system

## Requirements

- PHP 8.4+
- Laravel 12.0+

## Installation

You can install the package via composer:

```bash
composer require webard/laravel-access-control
```

## Usage

### 1. Define Permissions as Enums

Create an enum that implements `PermissionDefinition`:

```php
<?php

namespace App\Permissions;

use Webard\LaravelAccessControl\Contracts\PermissionDefinition;

enum ProductPermission: string implements PermissionDefinition
{
    case View = 'product.view';
    case Create = 'product.create';
    case Update = 'product.update';
    case Delete = 'product.delete';
}
```

### 2. Register Permissions

Register your permission enums in a service provider:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Webard\LaravelAccessControl\PermissionRegistry;
use App\Permissions\ProductPermission;
use App\Permissions\CategoryPermission;

class PermissionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $registry = resolve(PermissionRegistry::class);
        
        $registry->register([
            ProductPermission::class,
            CategoryPermission::class,
        ]);
    }
}
```

### 3. Register Voters

Voters allow you to add custom authorization logic to permissions. The main advantage is that **voters can be registered from any module**, making them perfect for modular monolith architectures.

#### Using Closures

```php
<?php

use Illuminate\Auth\Access\Response;
use Webard\LaravelAccessControl\VoterRegistry;
use App\Models\User;
use App\Models\Channel;
use App\Permissions\ChannelPermission;

$registry = resolve(VoterRegistry::class);

$registry->register(
    CategoryPermission::Delete,
    function (User $user, Category $category = null): Response {
        if ($category->products()->exists()) {
            return Response::deny(
                'Cannot delete category with assigned products.'
            );
        }

        return Response::allow();
    }
);
```

#### Using Voter Classes

For more complex logic, create dedicated voter classes with the `#[VoterForPermission]` attribute:

```php
<?php

namespace App\Voters;

use Illuminate\Auth\Access\Response;
use Webard\LaravelAccessControl\Attributes\VoterForPermission;
use App\Models\User;
use App\Models\Currency;
use App\Models\Channel;
use App\Permissions\CurrencyPermission;

final class CurrencyVoter
{
    #[VoterForPermission(CurrencyPermission::Delete)]
    public function preventDeletingUsedByChannels(User $user, Currency|string|null $currency): Response
    {
        if (!$currency instanceof Currency) {
            return Response::allow();
        }
        
        if (Channel::query()->whereJsonContains('currencies', $currency->code)->exists()) {
            return Response::deny('Some channels are using this currency.');
        }

        return Response::allow();
    }

    #[VoterForPermission(CurrencyPermission::Update)]
    public function preventUpdatingDefaultCurrency(User $user, Currency|string|null $currency): Response
    {
        if ($currency instanceof Currency && $currency->is_default) {
            return Response::deny('Cannot modify the default currency.');
        }

        return Response::allow();
    }
}
```

Register the voter class:

```php
$registry = resolve(VoterRegistry::class);

$registry->register(CurrencyVoter::class);

// Or register multiple classes at once
$registry->register([
    CurrencyVoter::class,
    ProductVoter::class,
    ChannelVoter::class,
]);
```

### 4. Using Authorization

The package integrates with Laravel's Gate, so you can use standard authorization methods:

```php
// Using Gate
Gate::allows(ProductPermission::View, $product);
Gate::authorize(ProductPermission::Delete, $product);

// Using the User model
$user->can(ProductPermission::Update, $product);
$user->cannot(ProductPermission::Delete, $product);

// In controllers
$this->authorize(ProductPermission::Update, $product);

// In Blade templates
@can(ProductPermission::View, $product)
    <a href="{{ route('products.show', $product) }}">View</a>
@endcan
```

### 5. Adding Permission Metadata (Optional)

Enhance your permissions with names, descriptions, and groups using PHP attributes:

#### Permission Groups

First, create a permission group:

```php
<?php

namespace App\Permissions\Groups;

use Webard\LaravelAccessControl\Contracts\PermissionGroupDefinition;

final class ProductGroup implements PermissionGroupDefinition
{
    public function getName(): string
    {
        return 'Products';
    }

    public function getDescription(): ?string
    {
        return 'Permissions related to product management';
    }

    public function getSlug(): string
    {
        return 'products';
    }
}
```

#### Enhanced Permission Enum

```php
<?php

namespace App\Permissions;

use Webard\LaravelAccessControl\Contracts\PermissionDefinition;
use Webard\LaravelAccessControl\Attributes\PermissionGroup;
use Webard\LaravelAccessControl\Attributes\PermissionName;
use Webard\LaravelAccessControl\Attributes\PermissionDescription;
use App\Permissions\Groups\ProductGroup;

#[PermissionGroup(ProductGroup::class)]
enum ProductPermission: string implements PermissionDefinition
{
    #[PermissionName('View Products')]
    #[PermissionDescription('Allows viewing product details')]
    case View = 'product.view';

    #[PermissionName('Create Products')]
    #[PermissionDescription('Allows creating new products')]
    case Create = 'product.create';

    #[PermissionName('Update Products')]
    #[PermissionDescription('Allows modifying existing products')]
    case Update = 'product.update';

    #[PermissionName('Delete Products')]
    #[PermissionDescription('Allows removing products from the system')]
    case Delete = 'product.delete';
}
```

#### Retrieving Permission Metadata

```php
use Webard\LaravelAccessControl\PermissionCollection;

$collection = resolve(PermissionCollection::class);

// Get all permissions grouped
$grouped = $collection->getGroupedPermissions();

// Get flat list of all permissions
$permissions = $collection->getPermissions();
```

This is useful for building permission management UIs.

## How Voters Work

1. When a permission check is performed via Laravel's Gate, the package first verifies if the user has the permission (via `$user->hasPermissionTo()`)
2. If the user has the permission, all registered voters for that permission are executed
3. **If any voter returns `Response::deny()`, the authorization fails**
4. Only if all voters return `Response::allow()`, the authorization succeeds

This allows different modules to add constraints to permissions without knowing about each other.

## Voters vs Policies

| Feature | Laravel Policies | Voters |
|---------|-----------------|--------|
| Location | Single class per model | Can be anywhere |
| Modularity | Coupled to model | Fully decoupled |
| Multiple handlers | No | Yes |
| Cross-module logic | Difficult | Easy |
| Registration | Automatic by convention | Explicit |

## User Model Requirements

Your User model must implement a `hasPermissionTo` method:

```php
public function hasPermissionTo(PermissionDefinition $permission): bool
{
    // Your permission checking logic
    // e.g., check against roles, direct permissions, etc.
}
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
