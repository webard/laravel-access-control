<?php

declare(strict_types=1);

use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Webard\LaravelAccessControl\GateConfigurator;
use Webard\LaravelAccessControl\PermissionRegistry;
use Webard\LaravelAccessControl\Tests\Fixtures\Models\Category;
use Webard\LaravelAccessControl\Tests\Fixtures\Models\Product;
use Webard\LaravelAccessControl\Tests\Fixtures\Models\User;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\CategoryPermission;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\ProductPermission;
use Webard\LaravelAccessControl\Tests\Fixtures\Voters\CategoryVoter;
use Webard\LaravelAccessControl\Tests\Fixtures\Voters\ProductVoter;
use Webard\LaravelAccessControl\VoterRegistry;

beforeEach(function (): void {
    $this->permissionRegistry = resolve(PermissionRegistry::class);
    $this->voterRegistry = resolve(VoterRegistry::class);
    $this->gateConfigurator = resolve(GateConfigurator::class);

    $this->permissionRegistry->register([
        ProductPermission::class,
        CategoryPermission::class,
    ]);

    $this->voterRegistry->registerClass([
        ProductVoter::class,
        CategoryVoter::class,
    ]);

    $this->gateConfigurator->configure();

    $this->admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => 'password',
        'permissions' => [
            'product.view',
            'product.create',
            'product.update',
            'product.delete',
            'category.view',
            'category.create',
            'category.update',
            'category.delete',
        ],
    ]);
});

describe('Cross-Module Voter Integration', function (): void {
    it('allows deleting empty category', function (): void {
        $this->actingAs($this->admin);

        $category = Category::create(['name' => 'Empty Category']);

        expect(Gate::allows(CategoryPermission::Delete, $category))->toBeTrue();
    });

    it('denies deleting category with products', function (): void {
        $this->actingAs($this->admin);

        $category = Category::create(['name' => 'Category with Products']);
        Product::create([
            'name' => 'Product in Category',
            'category_id' => $category->id,
            'is_locked' => false,
        ]);

        expect(Gate::allows(CategoryPermission::Delete, $category))->toBeFalse();
    });

    it('allows deleting category after removing products', function (): void {
        $this->actingAs($this->admin);

        $category = Category::create(['name' => 'Category']);
        $product = Product::create([
            'name' => 'Product',
            'category_id' => $category->id,
            'is_locked' => false,
        ]);

        expect(Gate::allows(CategoryPermission::Delete, $category))->toBeFalse();

        $product->delete();

        expect(Gate::allows(CategoryPermission::Delete, $category))->toBeTrue();
    });

    it('supports multiple voters from different modules', function (): void {
        $this->actingAs($this->admin);

        // Add an additional voter for product deletion
        $this->voterRegistry->register(
            ProductPermission::Delete,
            function (User $user, ?Product $product = null): Response {
                if ($product && $product->name === 'Protected Product') {
                    return Response::deny('This product is protected.');
                }

                return Response::allow();
            }
        );

        // Reconfigure gate
        $this->gateConfigurator->configure();

        // Normal product - only original voter applies
        $normalProduct = Product::create(['name' => 'Normal Product', 'is_locked' => false]);
        expect(Gate::allows(ProductPermission::Delete, $normalProduct))->toBeTrue();

        // Locked product - original voter denies
        $lockedProduct = Product::create(['name' => 'Locked Product', 'is_locked' => true]);
        expect(Gate::allows(ProductPermission::Delete, $lockedProduct))->toBeFalse();

        // Protected product - new voter denies
        $protectedProduct = Product::create(['name' => 'Protected Product', 'is_locked' => false]);
        expect(Gate::allows(ProductPermission::Delete, $protectedProduct))->toBeFalse();
    });

    it('allows different permissions for same resource', function (): void {
        $this->actingAs($this->admin);

        $lockedProduct = Product::create(['name' => 'Locked Product', 'is_locked' => true]);

        // View has no voter, should be allowed
        expect(Gate::allows(ProductPermission::View, $lockedProduct))->toBeTrue();

        // Create has no voter, should be allowed
        expect(Gate::allows(ProductPermission::Create))->toBeTrue();

        // Update and Delete have voters that check is_locked
        expect(Gate::allows(ProductPermission::Update, $lockedProduct))->toBeFalse();
        expect(Gate::allows(ProductPermission::Delete, $lockedProduct))->toBeFalse();
    });
});

describe('Permission-based Authorization', function (): void {
    it('respects permission boundaries', function (): void {
        $limitedUser = User::create([
            'name' => 'Limited User',
            'email' => 'limited@example.com',
            'password' => 'password',
            'permissions' => ['product.view', 'category.view'],
        ]);

        $this->actingAs($limitedUser);

        $product = Product::create(['name' => 'Product', 'is_locked' => false]);
        $category = Category::create(['name' => 'Category']);

        // Can view
        expect(Gate::allows(ProductPermission::View, $product))->toBeTrue();
        expect(Gate::allows(CategoryPermission::View, $category))->toBeTrue();

        // Cannot modify
        expect(Gate::allows(ProductPermission::Update, $product))->toBeFalse();
        expect(Gate::allows(ProductPermission::Delete, $product))->toBeFalse();
        expect(Gate::allows(CategoryPermission::Delete, $category))->toBeFalse();
    });

    it('combines permission check with voter logic', function (): void {
        // User with delete permission
        $userWithDelete = User::create([
            'name' => 'Delete User',
            'email' => 'delete@example.com',
            'password' => 'password',
            'permissions' => ['product.delete'],
        ]);

        $this->actingAs($userWithDelete);

        $lockedProduct = Product::create(['name' => 'Locked', 'is_locked' => true]);
        $unlockedProduct = Product::create(['name' => 'Unlocked', 'is_locked' => false]);

        // Has permission but voter denies (locked)
        expect(Gate::allows(ProductPermission::Delete, $lockedProduct))->toBeFalse();

        // Has permission and voter allows (unlocked)
        expect(Gate::allows(ProductPermission::Delete, $unlockedProduct))->toBeTrue();
    });
});
