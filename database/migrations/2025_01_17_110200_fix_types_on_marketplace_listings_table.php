<?php

use Illuminate\Database\Migrations\Migration;
use Enjin\Platform\Marketplace\Models\MarketplaceListing;
use Enjin\Platform\Marketplace\Enums\ListingType;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        MarketplaceListing::where('type', ListingType::FIXED_PRICE->value)->update(['type' => ListingType::FIXED_PRICE->name]);
        MarketplaceListing::where('type', ListingType::AUCTION->value)->update(['type' => ListingType::AUCTION->name]);
        MarketplaceListing::where('type', ListingType::OFFER->value)->update(['type' => ListingType::OFFER->name]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
