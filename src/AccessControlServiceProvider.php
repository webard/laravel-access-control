<?php

namespace Webard\LaravelAccessControl;

use Illuminate\Support\ServiceProvider;

class AccessControlServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(fn (): PermissionRegistry => new PermissionRegistry);
        $this->app->singleton(fn (): VoterRegistry => new VoterRegistry);

    }

    public function boot()
    {

        resolve(GateConfigurator::class)->configure();
    }
}
