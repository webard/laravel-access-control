<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Traits;

use BackedEnum;
use Webard\LaravelAccessControl\Contracts\AuthControllable;

/**
 * @phpstan-ignore trait.unused
 */
trait HasRoles
{
    /**
     * Per-instance memo of resolved permission checks. Authorization gates are
     * evaluated many times per request (once per nav item / action), so caching
     * the result avoids re-iterating roles for the same permission. The cache
     * lives on the model instance, which is request-scoped.
     *
     * @var array<string, bool>
     */
    private array $resolvedRolePermissions = [];

    public function hasPermissionTo($permission): bool
    {
        $key = $permission instanceof BackedEnum ? (string) $permission->value : (string) $permission;

        // Note: ??= does not re-evaluate a cached `false` (only null/unset).
        return $this->resolvedRolePermissions[$key] ??= $this->resolveRolePermission($permission);
    }

    private function resolveRolePermission($permission): bool
    {
        foreach ($this->getRoles() as $role) {
            if ($role->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clear the per-instance permission memo. Call after the user's roles or
     * permissions change within the same request.
     */
    public function forgetResolvedPermissions(): void
    {
        $this->resolvedRolePermissions = [];
    }

    /**
     * @return iterable<AuthControllable>
     */
    abstract public function getRoles(): iterable;
}
