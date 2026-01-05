<?php

namespace Webard\LaravelAccessControl;

use Filament\Panel;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Livewire\Features\SupportTesting\Testable;
use Webard\LaravelAccessControl\VoterRegistry;
use Webard\LaravelAccessControl\GateConfigurator;
use Webard\LaravelAccessControl\PermissionRegistry;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Webard\LaravelAccessControl\Testing\TestsAccessControl;
use Webard\LaravelAccessControl\Filament\AccessControlPlugin;

class AccessControlServiceProvider extends ServiceProvider
{
    public function register()
    {
         $this->app->singleton(fn (): PermissionRegistry => new PermissionRegistry);
        $this->app->singleton(fn (): VoterRegistry => new VoterRegistry);

    }

    public function boot() {


        resolve(GateConfigurator::class)->configure();
    }
}
