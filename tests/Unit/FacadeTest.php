<?php

declare(strict_types=1);

use Webard\LaravelAccessControl\AccessControl;
use Webard\LaravelAccessControl\Facades\AccessControl as AccessControlFacade;

describe('AccessControl Facade', function (): void {
    it('has the correct facade accessor', function (): void {
        // Use reflection to check the protected getFacadeAccessor method
        $reflection = new ReflectionClass(AccessControlFacade::class);
        $method = $reflection->getMethod('getFacadeAccessor');
        $method->setAccessible(true);

        // Create a temporary instance to call the method
        $instance = new AccessControlFacade;
        $accessor = $method->invoke($instance);

        expect($accessor)->toBe(AccessControl::class);
    });
});
