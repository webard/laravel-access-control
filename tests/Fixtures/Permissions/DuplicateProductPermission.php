<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Tests\Fixtures\Permissions;

use Webard\LaravelAccessControl\Attributes\PermissionGroup;
use Webard\LaravelAccessControl\Attributes\PermissionName;
use Webard\LaravelAccessControl\Contracts\PermissionDefinition;

#[PermissionGroup('Product', 'Product Management')]
enum DuplicateProductPermission: string implements PermissionDefinition
{
    #[PermissionName('View Products')]
    case View = 'product.view'; // Same as ProductPermission::View

    #[PermissionName('Create Product')]
    case Create = 'product.create';
}
