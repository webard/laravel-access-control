<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Contracts;

interface HasPermissionsContract
{
    public function hasPermissionTo(PermissionDefinition $permission): bool;
}
