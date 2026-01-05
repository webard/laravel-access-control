<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Tests\Fixtures\Permissions;

use Webard\LaravelAccessControl\Contracts\PermissionDefinition;

// Permission enum without PermissionGroup attribute - used for testing
enum PermissionWithoutGroup: string implements PermissionDefinition
{
    case Test = 'test.action';
}
