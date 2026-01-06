<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Traits;

/**
 * @phpstan-ignore trait.unused
 */
trait HasRolesAndPermissions
{
    use HasPermissions {
        HasPermissions::hasPermissionTo as hasDirectPermissionTo;
    }
    use HasRoles {
        HasRoles::hasPermissionTo as hasRolePermissionTo;
    }

    public function hasPermissionTo($permission): bool
    {
        if ($this->hasDirectPermissionTo($permission)) {
            return true;
        }

        return (bool) $this->hasRolePermissionTo($permission);
    }
}
