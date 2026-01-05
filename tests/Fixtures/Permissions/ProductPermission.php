<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Tests\Fixtures\Permissions;

use Webard\LaravelAccessControl\Attributes\PermissionDescription;
use Webard\LaravelAccessControl\Attributes\PermissionGroup;
use Webard\LaravelAccessControl\Attributes\PermissionName;
use Webard\LaravelAccessControl\Contracts\PermissionDefinition;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\Groups\ProductGroup;

#[PermissionGroup(ProductGroup::class)]
enum ProductPermission: string implements PermissionDefinition
{
    #[PermissionName('View Products')]
    #[PermissionDescription('Allows viewing product details')]
    case View = 'product.view';

    #[PermissionName('Create Products')]
    #[PermissionDescription('Allows creating new products')]
    case Create = 'product.create';

    #[PermissionName('Update Products')]
    case Update = 'product.update';

    case Delete = 'product.delete';
}
