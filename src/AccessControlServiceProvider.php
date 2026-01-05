<?php

namespace Webard\LaravelAccessControl;

use Illuminate\Support\ServiceProvider;

class AccessControlServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(fn (): PermissionRegistry => new PermissionRegistry);
        $this->app->singleton(fn (): VoterRegistry => new VoterRegistry);
    }

    public function boot(): void
    {
        resolve(GateConfigurator::class)->configure();
    }
}
