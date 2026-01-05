<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Gate;
use Webard\LaravelAccessControl\GateConfigurator;
use Webard\LaravelAccessControl\PermissionRegistry;
use Webard\LaravelAccessControl\Tests\Fixtures\Models\Product;
use Webard\LaravelAccessControl\Tests\Fixtures\Models\User;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\ProductPermission;
use Webard\LaravelAccessControl\Tests\Fixtures\Voters\ProductVoter;
use Webard\LaravelAccessControl\VoterRegistry;

beforeEach(function (): void {
    $this->permissionRegistry = resolve(PermissionRegistry::class);
    $this->voterRegistry = resolve(VoterRegistry::class);
    $this->gateConfigurator = resolve(GateConfigurator::class);

    $this->permissionRegistry->register(ProductPermission::class);
    $this->voterRegistry->registerClass(ProductVoter::class);
    $this->gateConfigurator->configure();

    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'permissions' => [
            'product.view',
            'product.create',
            'product.update',
            'product.delete',
        ],
    ]);
});

describe('GateConfigurator', function (): void {
    describe('authorization without voters', function (): void {
        it('allows user with permission', function (): void {
            $this->actingAs($this->user);

            expect(Gate::allows(ProductPermission::View))->toBeTrue();
            expect(Gate::allows(ProductPermission::Create))->toBeTrue();
        });

        it('denies user without permission', function (): void {
            $userWithoutPermissions = User::create([
                'name' => 'No Permissions User',
                'email' => 'noperm@example.com',
                'password' => 'password',
                'permissions' => [],
            ]);

            $this->actingAs($userWithoutPermissions);

            expect(Gate::allows(ProductPermission::View))->toBeFalse();
            expect(Gate::allows(ProductPermission::Create))->toBeFalse();
        });

        it('denies unauthenticated user', function (): void {
            expect(Gate::allows(ProductPermission::View))->toBeFalse();
        });
    });

    describe('authorization with voters', function (): void {
        it('allows when user has permission and voter allows', function (): void {
            $this->actingAs($this->user);

            $product = Product::create(['name' => 'Unlocked Product', 'is_locked' => false]);

            expect(Gate::allows(ProductPermission::Delete, $product))->toBeTrue();
            expect(Gate::allows(ProductPermission::Update, $product))->toBeTrue();
        });

        it('denies when voter denies even if user has permission', function (): void {
            $this->actingAs($this->user);

            $lockedProduct = Product::create(['name' => 'Locked Product', 'is_locked' => true]);

            expect(Gate::allows(ProductPermission::Delete, $lockedProduct))->toBeFalse();
            expect(Gate::allows(ProductPermission::Update, $lockedProduct))->toBeFalse();
        });

        it('denies when user lacks permission even if voter would allow', function (): void {
            $userWithoutDelete = User::create([
                'name' => 'Limited User',
                'email' => 'limited@example.com',
                'password' => 'password',
                'permissions' => ['product.view'],
            ]);

            $this->actingAs($userWithoutDelete);

            $product = Product::create(['name' => 'Unlocked Product', 'is_locked' => false]);

            expect(Gate::allows(ProductPermission::Delete, $product))->toBeFalse();
        });
    });

    describe('using User model methods', function (): void {
        it('works with can method', function (): void {
            $product = Product::create(['name' => 'Unlocked Product', 'is_locked' => false]);

            expect($this->user->can(ProductPermission::View))->toBeTrue();
            expect($this->user->can(ProductPermission::Delete, $product))->toBeTrue();
        });

        it('works with cannot method', function (): void {
            $lockedProduct = Product::create(['name' => 'Locked Product', 'is_locked' => true]);

            expect($this->user->cannot(ProductPermission::Delete, $lockedProduct))->toBeTrue();
        });
    });
});
