<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Tests\Fixtures\Models;

use Illuminate\Support\Collection;
use Webard\LaravelAccessControl\Contracts\AuthControllable;
use Webard\LaravelAccessControl\Traits\HasRoles;

class Role implements AuthControllable
{
    use HasRoles;

    public function __construct(
        private Collection $roles = new Collection,
    ) {}

    public function getRoles(): iterable
    {
        return $this->roles;
    }

    public function hasPermissionTo($permission): bool
    {
        return false;
    }
}
