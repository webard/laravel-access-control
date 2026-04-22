<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Dto;

use Illuminate\Support\Collection;

final class PermissionGroupDto
{
    public function __construct(
        public string $name,
        public string $slug,
        /**
         * @var Collection<int, PermissionDto>
         */
        public Collection $children,
        public ?string $description = null,
    ) {}
}
