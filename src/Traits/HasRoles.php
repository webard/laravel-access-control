<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Traits;

use Webard\LaravelAccessControl\Contracts\HasPermissionsContract;

/**
 * @phpstan-ignore trait.unused
 */
trait HasRoles
{
    public function hasPermissionTo($permission): bool
    {
        foreach ($this->getRoles() as $role) {
            if ($role->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return iterable<HasPermissionsContract>
     */
    abstract public function getRoles(): iterable;
}
