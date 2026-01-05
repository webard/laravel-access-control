<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Tests\Fixtures\Voters;

use Illuminate\Auth\Access\Response;
use Webard\LaravelAccessControl\Attributes\VoterForPermission;
use Webard\LaravelAccessControl\Tests\Fixtures\Models\Product;
use Webard\LaravelAccessControl\Tests\Fixtures\Models\User;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\ProductPermission;

final class ProductVoter
{
    #[VoterForPermission(ProductPermission::Delete)]
    public function preventDeletingLockedProducts(User $user, Product | string | null $product = null): Response
    {
        if (! $product instanceof Product) {
            return Response::allow();
        }

        if ($product->is_locked) {
            return Response::deny('Cannot delete a locked product.');
        }

        return Response::allow();
    }

    #[VoterForPermission(ProductPermission::Update)]
    public function preventUpdatingLockedProducts(User $user, Product | string | null $product = null): Response
    {
        if (! $product instanceof Product) {
            return Response::allow();
        }

        if ($product->is_locked) {
            return Response::deny('Cannot update a locked product.');
        }

        return Response::allow();
    }
}
