<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Attributes;

use Attribute;
use Webard\LaravelAccessControl\Contracts\PermissionGroupDefinition;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class PermissionGroup
{
    public function __construct(
        /**
         * @var class-string<PermissionGroupDefinition> $group
         */
        public string $group,
    ) {}
}
