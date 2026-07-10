# Laravel Access Control

> [!IMPORTANT]
> This project has moved to **[happenv-com/laravel-access-control](https://github.com/happenv-com/laravel-access-control)**. Please update your references and follow the new repository for future development.

<picture>
  <source media="(prefers-color-scheme: dark)" srcset="https://banners.beyondco.de/Laravel%20Access%20Control.png?theme=dark&packageManager=composer+require&packageName=webard%2Flaravel-access-control&pattern=wiggle&style=style_1&description=Control+access+using+Enums+and+Permission+Voters&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg">
  <img alt="Laravel Access Control" src="https://banners.beyondco.de/Laravel%20Access%20Control.png?theme=light&packageManager=composer+require&packageName=webard%2Flaravel-access-control&pattern=wiggle&style=style_1&description=Control+access+using+Enums+and+Permission+Voters&md=1&showWatermark=0&fontSize=100px&images=https%3A%2F%2Flaravel.com%2Fimg%2Flogomark.min.svg">
</picture>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/webard/laravel-access-control.svg?style=flat-square)](https://packagist.org/packages/webard/laravel-access-control)
[![Total Downloads](https://img.shields.io/packagist/dt/webard/laravel-access-control.svg?style=flat-square)](https://packagist.org/packages/webard/laravel-access-control)

A modular access control library for Laravel applications that uses **enum-based permissions** and a **voter system**. Perfect for modular monolith architectures where different modules can define their own permission logic and extend existing.

### Example

Imagine two modules: **Product** and **ProductGallery**. The Product module knows nothing about ProductGallery, but ProductGallery should block product deletion until all galleries are removed.

**Product module** defines the permission:
```php
enum ProductPermission: string implements PermissionDefinition
{
    case Delete = 'product.delete';
}
```

**ProductGallery module** registers a voter to add its constraint:
```php
// In ProductGalleryServiceProvider
$registry = resolve(VoterRegistry::class);

$registry->register(
    ProductPermission::Delete,

    function (User $user, Product $product = null): Response {
        if ($product && $product->galleries()->exists()) {
            return Response::deny('Cannot delete product with galleries.');
        }
        return Response::allow();
    }
);
```

This library allows achieving such behavior without tightly coupling the two modules.

## Key Features

- 🔐 **Enum-based permissions** - Define permissions as PHP enums for type-safety and IDE autocomplete
- 🗳️ **Voter system** - Replace Laravel Policies with flexible voters that can be registered from any module
- 📦 **Modular architecture** - Each module can register its own voters without modifying core logic
- 🏷️ **Permission metadata** - Add names, descriptions, and groups to permissions via PHP attributes
- ⚡ **Laravel Gate integration** - Works seamlessly with Laravel's authorization system

## When to Use This Package

This package is designed primarily for **modular monolith architectures** where your application is split into independent modules (e.g., using [nWidart/laravel-modules](https://github.com/nWidart/laravel-modules) or [InterNACHI/modular](https://github.com/InterNACHI/modular)).

The key advantage of this package is that **voters can be registered from any module**, allowing each module to define its own authorization constraints without modifying the core application or other modules.

### When NOT to Use This Package

If you're building a traditional Laravel monolith without modular architecture, you probably don't need this package. In that case, the following solutions are sufficient:

- **[Laravel Policies](https://laravel.com/docs/authorization#creating-policies)** - Built-in authorization system, perfect for simple applications
- **[spatie/laravel-permission](https://github.com/spatie/laravel-permission)** - Excellent package for role and permission management in monolithic applications

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

> **Tip:** In modular applications, consider prefixing permission values with your module name (e.g., `pim-module.product.view`, `inventory-module.stock.update`) to avoid conflicts between modules and make it clear which module owns each permission.

### 2. Register Permissions

Register your permission enums in a service provider:

```php
<?php

namespace Modules\Category\Providers;

use Illuminate\Support\ServiceProvider;
use Webard\LaravelAccessControl\PermissionRegistry;
use Modules\Category\Permissions\CategoryPermission;

class CategoryModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $registry = resolve(PermissionRegistry::class);
        
        $registry->register([
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

namespace Modules\Product\Providers;

use Illuminate\Auth\Access\Response;
use Illuminate\Support\ServiceProvider;
use Webard\LaravelAccessControl\PermissionRegistry;
use Webard\LaravelAccessControl\VoterRegistry;
use App\Models\User;
use Modules\Category\Models\Category;
use Modules\Category\Permissions\CategoryPermission;
use Modules\Product\Permissions\ProductPermission;

class ProductModuleServiceProvider extends ServiceProvider
{
    public function boot(): void {
        $registry = resolve(PermissionRegistry::class);
        
        $registry->register([
            ProductPermission::class,
        ]);

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
    }
}
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

namespace Modules\Product\Permissions;

use Webard\LaravelAccessControl\Contracts\PermissionDefinition;
use Webard\LaravelAccessControl\Attributes\PermissionGroup;
use Webard\LaravelAccessControl\Attributes\PermissionName;
use Webard\LaravelAccessControl\Attributes\PermissionDescription;
use Modules\Product\PermissionGroups\ProductGroup;

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

## Model Setup

The model on which authorization checks are performed (typically `User`) must implement the `AuthControllable` interface.

This package provides three traits for managing permissions:

### HasRoles (Recommended)

Use this trait when users receive permissions **only through roles**. This is the recommended approach for most applications.

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Webard\LaravelAccessControl\Contracts\AuthControllable;
use Webard\LaravelAccessControl\Traits\HasRoles;

class User extends Authenticatable implements AuthControllable
{
    use HasRoles;

    /**
     * Get roles assigned to the user.
     */
    public function getRoles(): iterable
    {
        return $this->roles; // Your roles relationship
    }
}
```

### HasPermissions

Use this trait for models that store permissions directly (e.g., a `Role` model). This trait provides `givePermissionTo()` and `revokePermissionTo()` methods.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Webard\LaravelAccessControl\Contracts\AuthControllable;
use Webard\LaravelAccessControl\Traits\HasPermissions;

class Role extends Model implements AuthControllable
{
    use HasPermissions;

    protected $casts = [
        'permissions' => 'array',
    ];

    protected function getPermissions(): Collection
    {
        return new Collection($this->permissions ?? []);
    }

    protected function setPermissions(Collection $permissions): void
    {
        $this->permissions = $permissions->toArray();
        $this->save();
    }
}
```

> You can also use this trait directly on User.

### HasRolesAndPermissions

Use this trait when users can receive permissions **both through roles AND directly**. Permissions are checked in both sources.

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Webard\LaravelAccessControl\Contracts\AuthControllable;
use Webard\LaravelAccessControl\Traits\HasRolesAndPermissions;

class User extends Authenticatable implements AuthControllable
{
    use HasRolesAndPermissions;

    protected $casts = [
        'permissions' => 'array',
    ];

    public function getRoles(): iterable
    {
        return $this->roles;
    }

    protected function getPermissions(): Collection
    {
        return new Collection($this->permissions ?? []);
    }

    protected function setPermissions(Collection $permissions): void
    {
        $this->permissions = $permissions->toArray();
        $this->save();
    }
}
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
