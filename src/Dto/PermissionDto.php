<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Dto;

use Webard\LaravelAccessControl\Contracts\PermissionDefinition;

final class PermissionDto
{
    public function __construct(
        public string $name,
        public PermissionDefinition $enum,
        public string $slug,
        public ?string $description = null,
    ) {}
}
