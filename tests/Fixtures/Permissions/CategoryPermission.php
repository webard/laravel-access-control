<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Tests\Fixtures\Permissions;

use Webard\LaravelAccessControl\Attributes\PermissionGroup;
use Webard\LaravelAccessControl\Contracts\PermissionDefinition;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\Groups\CategoryGroup;

#[PermissionGroup(CategoryGroup::class)]
enum CategoryPermission: string implements PermissionDefinition
{
    case View = 'category.view';
    case Create = 'category.create';
    case Update = 'category.update';
    case Delete = 'category.delete';
}
