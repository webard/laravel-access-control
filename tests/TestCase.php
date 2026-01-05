<?php

declare(strict_types=1);

namespace Webard\LaravelAccessControl\Tests;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use Override;
use Webard\LaravelAccessControl\AccessControlServiceProvider;

class TestCase extends Orchestra
{
    use LazilyRefreshDatabase;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Override]
    protected function getPackageProviders($app): array
    {
        return [
            AccessControlServiceProvider::class,
        ];
    }

    #[Override]
    protected function getEnvironmentSetUp($app): void
    {
        $app->make('config')->set('database.default', 'testing');
        $app->make('config')->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    #[Override]
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Fixtures/database/migrations');
    }
}
