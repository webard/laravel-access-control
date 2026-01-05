<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Attributes;

use Attribute;
use Webard\LaravelAccessControl\Contracts\PermissionDefinition;

#[Attribute(Attribute::TARGET_METHOD)]
final class VoterForPermission
{
    public function __construct(
        public PermissionDefinition $permission,
    ) {}
}
