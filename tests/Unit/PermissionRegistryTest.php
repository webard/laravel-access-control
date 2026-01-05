<?php

declare(strict_types=1);

use Webard\LaravelAccessControl\Exceptions\PermissionAlreadyRegisteredException;
use Webard\LaravelAccessControl\PermissionRegistry;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\CategoryPermission;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\ProductPermission;

beforeEach(function (): void {
    $this->registry = new PermissionRegistry;
});

describe('PermissionRegistry', function (): void {
    it('can register a single permission enum', function (): void {
        $this->registry->register(ProductPermission::class);

        expect($this->registry->permissionDefinitions)
            ->toContain(ProductPermission::class)
            ->and($this->registry->permissions)
            ->toHaveKey('product.view')
            ->toHaveKey('product.create')
            ->toHaveKey('product.update')
            ->toHaveKey('product.delete');
    });

    it('can register multiple permission enums at once', function (): void {
        $this->registry->register([
            ProductPermission::class,
            CategoryPermission::class,
        ]);

        expect($this->registry->permissionDefinitions)
            ->toContain(ProductPermission::class)
            ->toContain(CategoryPermission::class)
            ->and($this->registry->permissions)
            ->toHaveKey('product.view')
            ->toHaveKey('category.view');
    });

    it('throws exception when registering same enum twice', function (): void {
        $this->registry->register(ProductPermission::class);

        expect(fn () => $this->registry->register(ProductPermission::class))
            ->toThrow(PermissionAlreadyRegisteredException::class);
    });

    it('throws exception when registering permission with duplicate value', function (): void {
        $this->registry->register(ProductPermission::class);

        // CategoryPermission has different values, so this should work
        expect(fn () => $this->registry->register(CategoryPermission::class))
            ->not->toThrow(PermissionAlreadyRegisteredException::class);
    });

    it('can check if definition is registered', function (): void {
        expect($this->registry->isDefined(ProductPermission::class))->toBeFalse();

        $this->registry->register(ProductPermission::class);

        expect($this->registry->isDefined(ProductPermission::class))->toBeTrue();
        expect($this->registry->isDefined(CategoryPermission::class))->toBeFalse();
    });

    it('stores permission enum instances in permissions array', function (): void {
        $this->registry->register(ProductPermission::class);

        expect($this->registry->permissions['product.view'])
            ->toBe(ProductPermission::View)
            ->and($this->registry->permissions['product.delete'])
            ->toBe(ProductPermission::Delete);
    });
});
