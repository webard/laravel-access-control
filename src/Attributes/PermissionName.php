<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Attributes;

use Attribute;

#[Attribute]
final readonly class PermissionName
{
    public function __construct(
        public string $value,
    ) {}
}
