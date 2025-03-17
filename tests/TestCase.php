<?php

namespace Enjin\Platform\Marketplace\Tests;

use Enjin\Platform\CoreServiceProvider;
use Enjin\Platform\Enums\Global\PlatformCache;
use Enjin\Platform\Marketplace\MarketplaceServiceProvider;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Fake events flag.
     */
    protected bool $fakeEvents = true;

    /**
     * The package providers.
     */
    #[\Override]
    protected function getPackageProviders($app)
    {
        return [
            CoreServiceProvider::class,
            MarketplaceServiceProvider::class,
        ];
    }

    /**
     * Define environment.
     */
    #[\Override]
    protected function defineEnvironment($app)
    {
        // Make sure, our .env file is loaded for local tests
        $app->useEnvironmentPath(__DIR__ . '/..');
        $app->useDatabasePath(__DIR__ . '/../database');
        $app->bootstrapWith([LoadEnvironmentVariables::class]);

        Cache::rememberForever(PlatformCache::SPEC_VERSION->key(currentMatrix()->value), fn () => 1020);
        Cache::rememberForever(PlatformCache::TRANSACTION_VERSION->key(currentMatrix()->value), fn () => 11);

        $app['config']->set('database.default', env('DB_DRIVER', 'mysql'));

        // MySQL config
        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', 'password'),
            'database' => env('DB_DATABASE', 'platform'),
            'port' => env('DB_PORT', '3306'),
            'prefix' => '',
        ]);

        if ($this->fakeEvents) {
            Event::fake();
        }
    }

    /**
     * Uses null daemon account.
     */
    protected function usesNullDaemonAccount($app)
    {
        $app->config->set('enjin-platform.chains.daemon-account', '0x0000000000000000000000000000000000000000000000000000000000000000');
    }

    /**
     * Uses enjin network.
     */
    protected function usesEnjinNetwork($app)
    {
        $app->config->set('enjin-platform.chains.network', 'enjin');
    }

    /**
     * Uses developer network.
     */
    protected function usesDeveloperNetwork($app)
    {
        $app->config->set('enjin-platform.chains.network', 'developer');
    }

    protected function assertArrayContainsArray(array $expected, array $actual): void
    {
        $this->assertArrayIsEqualToArrayOnlyConsideringListOfKeys($expected, $actual, $this->arrayKeys($expected));
    }

    protected function arrayKeys($array): array
    {
        return array_keys(Arr::dot($array));
    }
}
