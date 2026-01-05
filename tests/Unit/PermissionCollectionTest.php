<?php

declare(strict_types=1);

use Webard\LaravelAccessControl\Dto\PermissionDto;
use Webard\LaravelAccessControl\Dto\PermissionGroupDto;
use Webard\LaravelAccessControl\PermissionCollection;
use Webard\LaravelAccessControl\PermissionRegistry;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\CategoryPermission;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\ProductPermission;

beforeEach(function (): void {
    $this->registry = new PermissionRegistry;
    $this->collection = new PermissionCollection($this->registry);
});

describe('PermissionCollection', function (): void {
    describe('getGroupedPermissions', function (): void {
        it('returns empty collection when no permissions registered', function (): void {
            $grouped = $this->collection->getGroupedPermissions();

            expect($grouped)->toBeEmpty();
        });

        it('returns permissions grouped by their group', function (): void {
            $this->registry->register([
                ProductPermission::class,
                CategoryPermission::class,
            ]);

            $grouped = $this->collection->getGroupedPermissions();

            expect($grouped)->toHaveCount(2);
            expect($grouped->keys()->toArray())->toContain('products');
            expect($grouped->keys()->toArray())->toContain('categories');
        });

        it('returns PermissionGroupDto instances', function (): void {
            $this->registry->register(ProductPermission::class);

            $grouped = $this->collection->getGroupedPermissions();

            expect($grouped->first())->toBeInstanceOf(PermissionGroupDto::class);
        });

        it('includes group metadata', function (): void {
            $this->registry->register(ProductPermission::class);

            $grouped = $this->collection->getGroupedPermissions();
            $productGroup = $grouped->get('products');

            expect($productGroup->name)->toBe('Products');
            expect($productGroup->slug)->toBe('products');
            expect($productGroup->description)->toBe('Permissions related to product management');
        });

        it('includes children permissions in group', function (): void {
            $this->registry->register(ProductPermission::class);

            $grouped = $this->collection->getGroupedPermissions();
            $productGroup = $grouped->get('products');

            expect($productGroup->children)->toHaveCount(4);
            expect($productGroup->children->first())->toBeInstanceOf(PermissionDto::class);
        });
    });

    describe('getPermissions', function (): void {
        it('returns empty collection when no permissions registered', function (): void {
            $permissions = $this->collection->getPermissions();

            expect($permissions)->toBeEmpty();
        });

        it('returns flat list of all permissions', function (): void {
            $this->registry->register([
                ProductPermission::class,
                CategoryPermission::class,
            ]);

            $permissions = $this->collection->getPermissions();

            expect($permissions)->toHaveCount(8); // 4 + 4
        });

        it('returns PermissionDto instances', function (): void {
            $this->registry->register(ProductPermission::class);

            $permissions = $this->collection->getPermissions();

            expect($permissions->first())->toBeInstanceOf(PermissionDto::class);
        });

        it('includes all permission slugs', function (): void {
            $this->registry->register([
                ProductPermission::class,
                CategoryPermission::class,
            ]);

            $permissions = $this->collection->getPermissions();
            $slugs = $permissions->pluck('slug')->toArray();

            expect($slugs)
                ->toContain('product.view')
                ->toContain('product.create')
                ->toContain('category.view')
                ->toContain('category.delete');
        });
    });
});
