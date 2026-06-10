<?php

declare(strict_types=1);

use Webard\LaravelAccessControl\Tests\Fixtures\Models\User;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\CategoryPermission;
use Webard\LaravelAccessControl\Traits\HasRoles;

beforeEach(function (): void {
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);
});

describe('HasRoles trait', function (): void {
    it('returns true when a role has the permission', function (): void {
        // Create a class that uses HasRoles directly
        $userWithRoles = new class
        {
            use HasRoles;

            private array $rolesCollection = [];

            public function getRoles(): iterable
            {
                return $this->rolesCollection;
            }

            public function setRoles(array $roles): void
            {
                $this->rolesCollection = $roles;
            }
        };

        // Create a mock role that HAS the permission
        $role = new class
        {
            public function hasPermissionTo($permission): bool
            {
                return true; // This role has the permission
            }
        };

        $userWithRoles->setRoles([$role]);

        // This will iterate through roles and find the permission
        $result = $userWithRoles->hasPermissionTo(CategoryPermission::Create);
        expect($result)->toBeTrue();
    });

    it('iterates through all roles to check permissions', function (): void {
        $userWithRoles = new class
        {
            use HasRoles;

            private array $rolesCollection = [];

            public function getRoles(): iterable
            {
                return $this->rolesCollection;
            }

            public function setRoles(array $roles): void
            {
                $this->rolesCollection = $roles;
            }
        };

        // Create a role that doesn't have the permission
        $role1 = new class
        {
            public function hasPermissionTo($permission): bool
            {
                return false;
            }
        };

        $userWithRoles->setRoles([$role1]);

        // This will iterate through roles and check permissions
        $result = $userWithRoles->hasPermissionTo(CategoryPermission::Create);
        expect($result)->toBeFalse();
    });

    it('returns false when no roles are set', function (): void {
        $userWithRoles = new class
        {
            use HasRoles;

            public function getRoles(): iterable
            {
                return [];
            }
        };

        $result = $userWithRoles->hasPermissionTo(CategoryPermission::Create);
        expect($result)->toBeFalse();
    });

    it('memoises the resolution and does not re-iterate roles', function (): void {
        $userWithRoles = new class
        {
            use HasRoles;

            private array $rolesCollection = [];

            public function getRoles(): iterable
            {
                return $this->rolesCollection;
            }

            public function setRoles(array $roles): void
            {
                $this->rolesCollection = $roles;
            }
        };

        $role = new class
        {
            public int $calls = 0;

            public function hasPermissionTo($permission): bool
            {
                $this->calls++;

                return false;
            }
        };

        $userWithRoles->setRoles([$role]);

        $userWithRoles->hasPermissionTo(CategoryPermission::Create);
        $userWithRoles->hasPermissionTo(CategoryPermission::Create);
        $userWithRoles->hasPermissionTo(CategoryPermission::Create);

        // The role is consulted once; subsequent checks hit the per-instance memo.
        expect($role->calls)->toBe(1);

        // Clearing the memo forces re-evaluation.
        $userWithRoles->forgetResolvedPermissions();
        $userWithRoles->hasPermissionTo(CategoryPermission::Create);

        expect($role->calls)->toBe(2);
    });
});

describe('HasRolesAndPermissions trait', function (): void {
    it('checks direct permissions first', function (): void {
        $this->user->givePermissionTo(CategoryPermission::Delete);

        expect($this->user->hasPermissionTo(CategoryPermission::Delete))->toBeTrue();
    });

    it('returns false when user has no permissions or roles with permission', function (): void {
        expect($this->user->hasPermissionTo(CategoryPermission::Create))->toBeFalse();
    });

    it('it combines both traits correctly', function (): void {
        // This test ensures the trait properly calls both hasDirectPermissionTo and hasRolePermissionTo
        expect($this->user->hasPermissionTo(CategoryPermission::Create))->toBeFalse();
    });
});
