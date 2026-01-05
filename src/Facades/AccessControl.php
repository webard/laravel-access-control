<?php

namespace Webard\LaravelAccessControl\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Webard\LaravelAccessControl\AccessControl
 */
class AccessControl extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Webard\LaravelAccessControl\AccessControl::class;
    }
}
