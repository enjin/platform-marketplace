<?php

namespace Enjin\Platform\Marketplace;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MarketplaceServiceProvider extends PackageServiceProvider
{
    /**
     * Configure provider.
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('platform-marketplace')
            ->hasConfigFile(['enjin-platform-marketplace'])
            ->hasMigration('create_marketplace_listings_table')
            ->hasMigration('create_marketplace_bids_table')
            ->hasMigration('create_marketplace_sales_table')
            ->hasMigration('create_marketplace_states_table')
            ->hasTranslations();
    }

    /**
     * Register provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
    }

    /**
     * Boot provider.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function packageRegistered()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'enjin-platform-marketplace');
    }
}
