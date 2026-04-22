<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Traits;

use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Webard\LaravelAccessControl\Contracts\PermissionDefinition;

/**
 * @mixin Authenticatable
 *
 * @phpstan-ignore trait.unused
 */
trait HasPermissions
{
    public function hasPermissionTo(PermissionDefinition $permission): bool
    {
        return $this->getPermissions()->contains($permission->value);
    }

    public function givePermissionTo(PermissionDefinition $permission): void
    {
        if (! $this->hasPermissionTo($permission)) {
            $this->setPermissions($this->getPermissions()->push($permission->value));
        }
    }

    public function revokePermissionTo(PermissionDefinition $permission): void
    {
        $this->setPermissions($this->getPermissions()->filter(fn (string $perm): bool => $perm !== $permission->value)->values());
    }

    abstract protected function getPermissions(): Collection;

    abstract protected function setPermissions(Collection $permissions): void;
}
