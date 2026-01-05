<?php

declare(strict_types=1);

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable;
use Webard\LaravelAccessControl\Tests\Fixtures\Models\Product;
use Webard\LaravelAccessControl\Tests\Fixtures\Models\User;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\ProductPermission;
use Webard\LaravelAccessControl\Tests\Fixtures\Voters\ProductVoter;
use Webard\LaravelAccessControl\VoterRegistry;

beforeEach(function (): void {
    $this->registry = new VoterRegistry;
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);
});

describe('VoterRegistry', function (): void {
    describe('registration', function (): void {
        it('can register a voter closure for a permission', function (): void {
            $this->registry->register(
                ProductPermission::Delete,
                fn (Authenticatable $user): Response => Response::allow()
            );

            expect($this->registry->countVoters(ProductPermission::Delete))->toBe(1);
        });

        it('can register multiple voters for the same permission', function (): void {
            $this->registry->register(
                ProductPermission::Delete,
                fn (Authenticatable $user): Response => Response::allow()
            );

            $this->registry->register(
                ProductPermission::Delete,
                fn (Authenticatable $user): Response => Response::allow()
            );

            expect($this->registry->countVoters(ProductPermission::Delete))->toBe(2);
        });

        it('can register a voter class', function (): void {
            $this->registry->registerClass(ProductVoter::class);

            expect($this->registry->countVoters(ProductPermission::Delete))->toBe(1);
            expect($this->registry->countVoters(ProductPermission::Update))->toBe(1);
        });

        it('can register multiple voter classes at once', function (): void {
            $this->registry->register([
                ProductVoter::class,
            ]);

            expect($this->registry->countVoters(ProductPermission::Delete))->toBe(1);
            expect($this->registry->countVoters(ProductPermission::Update))->toBe(1);
        });

        it('throws exception when registering permission without closure', function (): void {
            expect(fn () => $this->registry->register(ProductPermission::Delete))
                ->toThrow(InvalidArgumentException::class);
        });
    });

    describe('voting', function (): void {
        it('returns allow when no voters are registered', function (): void {
            $result = $this->registry->vote(ProductPermission::View, $this->user);

            expect($result->allowed())->toBeTrue();
        });

        it('returns allow when all voters allow', function (): void {
            $this->registry->register(
                ProductPermission::Delete,
                fn (Authenticatable $user): Response => Response::allow()
            );

            $this->registry->register(
                ProductPermission::Delete,
                fn (Authenticatable $user): Response => Response::allow()
            );

            $result = $this->registry->vote(ProductPermission::Delete, $this->user);

            expect($result->allowed())->toBeTrue();
        });

        it('returns deny when any voter denies', function (): void {
            $this->registry->register(
                ProductPermission::Delete,
                fn (Authenticatable $user): Response => Response::allow()
            );

            $this->registry->register(
                ProductPermission::Delete,
                fn (Authenticatable $user): Response => Response::deny('Not allowed')
            );

            $result = $this->registry->vote(ProductPermission::Delete, $this->user);

            expect($result->denied())->toBeTrue();
            expect($result->message())->toBe('Not allowed');
        });

        it('stops at first denial', function (): void {
            $callCount = 0;

            $this->registry->register(
                ProductPermission::Delete,
                function (Authenticatable $user) use (&$callCount): Response {
                    $callCount++;

                    return Response::deny('First denial');
                }
            );

            $this->registry->register(
                ProductPermission::Delete,
                function (Authenticatable $user) use (&$callCount): Response {
                    $callCount++;

                    return Response::allow();
                }
            );

            $this->registry->vote(ProductPermission::Delete, $this->user);

            expect($callCount)->toBe(1);
        });

        it('passes arguments to voters', function (): void {
            $product = Product::create(['name' => 'Test Product', 'is_locked' => true]);

            $this->registry->registerClass(ProductVoter::class);

            $result = $this->registry->vote(ProductPermission::Delete, $this->user, $product);

            expect($result->denied())->toBeTrue();
            expect($result->message())->toBe('Cannot delete a locked product.');
        });

        it('allows deleting unlocked product', function (): void {
            $product = Product::create(['name' => 'Test Product', 'is_locked' => false]);

            $this->registry->registerClass(ProductVoter::class);

            $result = $this->registry->vote(ProductPermission::Delete, $this->user, $product);

            expect($result->allowed())->toBeTrue();
        });
    });
});
