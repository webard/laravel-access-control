<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Tests\Fixtures\Permissions\Groups;

use Webard\LaravelAccessControl\Contracts\PermissionGroupDefinition;

final class ProductGroup implements PermissionGroupDefinition
{
    public function getName(): string
    {
        return 'Products';
    }

    public function getDescription(): ?string
    {
        return 'Permissions related to product management';
    }

    public function getSlug(): string
    {
        return 'products';
    }
}
