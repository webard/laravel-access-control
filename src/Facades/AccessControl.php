<?php

namespace Webard\AccessControl\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Webard\AccessControl\AccessControl
 */
class AccessControl extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Webard\AccessControl\AccessControl::class;
    }
}
