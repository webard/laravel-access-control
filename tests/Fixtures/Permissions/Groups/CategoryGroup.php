<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Tests\Fixtures\Permissions\Groups;

use Webard\LaravelAccessControl\Contracts\PermissionGroupDefinition;

final class CategoryGroup implements PermissionGroupDefinition
{
    public function getName(): string
    {
        return 'Categories';
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getSlug(): string
    {
        return 'categories';
    }
}
