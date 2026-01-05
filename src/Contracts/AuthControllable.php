<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Contracts;

interface AuthControllable
{
    public function hasPermissionTo(PermissionDefinition $permission): bool;
}
