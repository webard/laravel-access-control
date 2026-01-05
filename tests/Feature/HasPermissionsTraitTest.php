<?php

declare(strict_types=1);

use Webard\LaravelAccessControl\Tests\Fixtures\Models\User;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\ProductPermission;

beforeEach(function (): void {
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'permissions' => [],
    ]);
});

describe('HasPermissions trait', function (): void {
    describe('hasPermissionTo', function (): void {
        it('returns false when user has no permissions', function (): void {
            expect($this->user->hasPermissionTo(ProductPermission::View))->toBeFalse();
            expect($this->user->hasPermissionTo(ProductPermission::Create))->toBeFalse();
        });

        it('returns true when user has the permission', function (): void {
            $this->user->update(['permissions' => ['product.view']]);

            expect($this->user->hasPermissionTo(ProductPermission::View))->toBeTrue();
            expect($this->user->hasPermissionTo(ProductPermission::Create))->toBeFalse();
        });

        it('returns true for multiple permissions', function (): void {
            $this->user->update(['permissions' => ['product.view', 'product.create', 'product.delete']]);

            expect($this->user->hasPermissionTo(ProductPermission::View))->toBeTrue();
            expect($this->user->hasPermissionTo(ProductPermission::Create))->toBeTrue();
            expect($this->user->hasPermissionTo(ProductPermission::Delete))->toBeTrue();
            expect($this->user->hasPermissionTo(ProductPermission::Update))->toBeFalse();
        });
    });

    describe('givePermissionTo', function (): void {
        it('adds permission to user', function (): void {
            $this->user->givePermissionTo(ProductPermission::View);

            expect($this->user->hasPermissionTo(ProductPermission::View))->toBeTrue();
            expect($this->user->fresh()->permissions)->toContain('product.view');
        });

        it('does not duplicate permission', function (): void {
            $this->user->givePermissionTo(ProductPermission::View);
            $this->user->givePermissionTo(ProductPermission::View);

            expect($this->user->fresh()->permissions)->toHaveCount(1);
        });

        it('can add multiple different permissions', function (): void {
            $this->user->givePermissionTo(ProductPermission::View);
            $this->user->givePermissionTo(ProductPermission::Create);
            $this->user->givePermissionTo(ProductPermission::Update);

            $permissions = $this->user->fresh()->permissions;

            expect($permissions)->toHaveCount(3);
            expect($permissions)->toContain('product.view');
            expect($permissions)->toContain('product.create');
            expect($permissions)->toContain('product.update');
        });
    });

    describe('revokePermissionTo', function (): void {
        it('removes permission from user', function (): void {
            $this->user->update(['permissions' => ['product.view', 'product.create']]);

            $this->user->revokePermissionTo(ProductPermission::View);

            expect($this->user->hasPermissionTo(ProductPermission::View))->toBeFalse();
            expect($this->user->hasPermissionTo(ProductPermission::Create))->toBeTrue();
        });

        it('does nothing when permission not present', function (): void {
            $this->user->update(['permissions' => ['product.view']]);

            $this->user->revokePermissionTo(ProductPermission::Create);

            expect($this->user->fresh()->permissions)->toHaveCount(1);
            expect($this->user->hasPermissionTo(ProductPermission::View))->toBeTrue();
        });

        it('can revoke all permissions', function (): void {
            $this->user->update(['permissions' => ['product.view', 'product.create']]);

            $this->user->revokePermissionTo(ProductPermission::View);
            $this->user->revokePermissionTo(ProductPermission::Create);

            expect($this->user->fresh()->permissions)->toBeEmpty();
        });
    });

    describe('persistence', function (): void {
        it('persists permissions to database', function (): void {
            $this->user->givePermissionTo(ProductPermission::View);

            $freshUser = User::find($this->user->id);

            expect($freshUser->hasPermissionTo(ProductPermission::View))->toBeTrue();
        });

        it('persists revoked permissions to database', function (): void {
            $this->user->update(['permissions' => ['product.view', 'product.create']]);

            $this->user->revokePermissionTo(ProductPermission::View);

            $freshUser = User::find($this->user->id);

            expect($freshUser->hasPermissionTo(ProductPermission::View))->toBeFalse();
            expect($freshUser->hasPermissionTo(ProductPermission::Create))->toBeTrue();
        });
    });
});
