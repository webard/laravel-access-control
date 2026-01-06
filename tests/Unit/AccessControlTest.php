<?php

declare(strict_types=1);

use Webard\LaravelAccessControl\AccessControl;
use Webard\LaravelAccessControl\PermissionRegistry;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\ProductPermission;
use Webard\LaravelAccessControl\Tests\Fixtures\Voters\ProductVoter;
use Webard\LaravelAccessControl\VoterRegistry;

beforeEach(function (): void {
    $this->voterRegistry = new VoterRegistry;
    $this->permissionRegistry = new PermissionRegistry;
    $this->accessControl = new AccessControl($this->voterRegistry, $this->permissionRegistry);
});

describe('AccessControl', function (): void {
    it('can register a voter class', function (): void {
        $this->accessControl->registerVoter(ProductVoter::class);

        expect($this->voterRegistry->countVoters(ProductPermission::Delete))->toBe(1);
    });

    it('can register a permission', function (): void {
        $this->accessControl->registerPermission(ProductPermission::class);

        expect($this->permissionRegistry->isDefined(ProductPermission::class))->toBeTrue();
    });

    it('can reflect permission', function (): void {
        $this->accessControl->reflectPermission(ProductPermission::class);
        // This method is currently a no-op, just ensure it doesn't throw
        expect(true)->toBeTrue();
    });
});
