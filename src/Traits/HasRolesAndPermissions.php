<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Traits;

/**
 * @phpstan-ignore trait.unused
 */
trait HasRolesAndPermissions
{
    use HasPermissions {
        hasPermissionTo as public hasDirectPermissionTo;
    }
    use HasRoles {
        hasPermissionTo as public hasRolePermissionTo;
    }

    public function hasPermissionTo($permission): bool
    {
        if ($this->hasDirectPermissionTo($permission)) {
            return true;
        }

        return (bool) $this->hasRolePermissionTo($permission);
    }
}
