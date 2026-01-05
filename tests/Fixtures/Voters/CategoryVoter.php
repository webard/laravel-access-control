<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Tests\Fixtures\Voters;

use Illuminate\Auth\Access\Response;
use Webard\LaravelAccessControl\Attributes\VoterForPermission;
use Webard\LaravelAccessControl\Tests\Fixtures\Models\Category;
use Webard\LaravelAccessControl\Tests\Fixtures\Models\User;
use Webard\LaravelAccessControl\Tests\Fixtures\Permissions\CategoryPermission;

final class CategoryVoter
{
    #[VoterForPermission(CategoryPermission::Delete)]
    public function preventDeletingCategoryWithProducts(User $user, Category | string | null $category = null): Response
    {
        if (! $category instanceof Category) {
            return Response::allow();
        }

        if ($category->products()->exists()) {
            return Response::deny('Cannot delete category with assigned products.');
        }

        return Response::allow();
    }
}
