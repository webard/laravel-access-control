<?php

declare(strict_types=1);

use Webard\LaravelAccessControl\Exceptions\PermissionGroupRequiredException;
use Webard\LaravelAccessControl\PermissionReflector;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\Groups\ProductGroup;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\PermissionWithoutGroup;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\ProductPermission;

describe('PermissionReflector', function (): void {
    describe('getGroup', function (): void {
        it('returns permission group instance', function (): void {
            $reflector = new PermissionReflector(ProductPermission::class);

            $group = $reflector->getGroup();

            expect($group)->toBeInstanceOf(ProductGroup::class);
            expect($group->getName())->toBe('Products');
            expect($group->getSlug())->toBe('products');
            expect($group->getDescription())->toBe('Permissions related to product management');
        });

        it('throws exception when group attribute is missing', function (): void {
            $reflector = new PermissionReflector(PermissionWithoutGroup::class);

            expect(fn () => $reflector->getGroup())
                ->toThrow(PermissionGroupRequiredException::class);
        });
    });

    describe('getValues', function (): void {
        it('returns collection of permission DTOs', function (): void {
            $reflector = new PermissionReflector(ProductPermission::class);

            $values = $reflector->getValues();

            expect($values)->toHaveCount(4);
        });

        it('includes custom name from attribute', function (): void {
            $reflector = new PermissionReflector(ProductPermission::class);

            $values = $reflector->getValues();
            $viewPermission = $values->firstWhere('slug', 'product.view');

            expect($viewPermission->name)->toBe('View Products');
        });

        it('includes custom description from attribute', function (): void {
            $reflector = new PermissionReflector(ProductPermission::class);

            $values = $reflector->getValues();
            $viewPermission = $values->firstWhere('slug', 'product.view');

            expect($viewPermission->description)->toBe('Allows viewing product details');
        });

        it('generates default name when attribute is missing', function (): void {
            $reflector = new PermissionReflector(ProductPermission::class);

            $values = $reflector->getValues();
            $deletePermission = $values->firstWhere('slug', 'product.delete');

            // Default name is generated from enum case name + group name
            expect($deletePermission->name)->toBe('Delete Products');
        });

        it('returns null description when attribute is missing', function (): void {
            $reflector = new PermissionReflector(ProductPermission::class);

            $values = $reflector->getValues();
            $deletePermission = $values->firstWhere('slug', 'product.delete');

            expect($deletePermission->description)->toBeNull();
        });

        it('includes enum instance in DTO', function (): void {
            $reflector = new PermissionReflector(ProductPermission::class);

            $values = $reflector->getValues();
            $viewPermission = $values->firstWhere('slug', 'product.view');

            expect($viewPermission->enum)->toBe(ProductPermission::View);
        });

        it('includes slug in DTO', function (): void {
            $reflector = new PermissionReflector(ProductPermission::class);

            $values = $reflector->getValues();

            expect($values->pluck('slug')->toArray())
                ->toContain('product.view')
                ->toContain('product.create')
                ->toContain('product.update')
                ->toContain('product.delete');
        });
    });
});
