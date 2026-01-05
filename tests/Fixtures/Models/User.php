<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Webard\LaravelAccessControl\Contracts\HasPermissionsContract;
use Webard\LaravelAccessControl\Traits\HasPermissions;

class User extends Authenticatable implements HasPermissionsContract
{
    use HasFactory;
    use HasPermissions;

    protected $fillable = [
        'name',
        'email',
        'password',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    protected function getPermissions(): Collection
    {
        return new Collection($this->permissions ?? []);
    }

    protected function setPermissions(Collection $permissions): void
    {
        $this->permissions = $permissions->toArray();
        $this->save();
    }
}
