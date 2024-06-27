<?php

namespace Enjin\Platform\Marketplace;

use Enjin\Platform\Facades\Package as EnjinPackage;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Codec\Encoder as MarketplaceEncoder;
use Enjin\Platform\Services\Processor\Substrate\Codec\Encoder as BaseEncoder;
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
            ->hasMigration('add_listing_id_to_marketplace_sales_table')
            ->hasMigration('drop_market_place_listing_id_in_marketplace_sales_table')
            ->hasMigration('nullable_listing_on_marketplace_sales_table')
            ->hasTranslations();
    }

    /**
     * Register provider.
     *
     * @return void
     */
    public function register()
    {
        if (app()->runningUnitTests()) {
            EnjinPackage::setPath(__DIR__ . '/..');
        }

        parent::register();

        BaseEncoder::setCallIndexKeys(array_merge(BaseEncoder::getCallIndexKeys(), MarketplaceEncoder::getCallIndexKeys()));
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
