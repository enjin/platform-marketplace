<?php

namespace Enjin\Platform\Marketplace\Enums\Substrate;

use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace\AuctionFinalized;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace\BidPlaced;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace\ListingCancelled;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace\ListingCreated;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace\ListingFilled;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;
use Enjin\Platform\Traits\EnumExtensions;

enum MarketplaceEventType: string
{
    use EnumExtensions;

    case AUCTION_FINALIZED = 'AuctionFinalized';
    case BID_PLACED = 'BidPlaced';
    case LISTING_CANCELLED = 'ListingCancelled';
    case LISTING_CREATED = 'ListingCreated';
    case LISTING_FILLED = 'ListingFilled';

    /**
     * Get the processor for the event.
     */
    public function getProcessor(): SubstrateEvent
    {
        return match ($this) {
            self::AUCTION_FINALIZED => new AuctionFinalized(),
            self::BID_PLACED => new BidPlaced(),
            self::LISTING_CANCELLED => new ListingCancelled(),
            self::LISTING_CREATED => new ListingCreated(),
            self::LISTING_FILLED => new ListingFilled(),
        };
    }
}
