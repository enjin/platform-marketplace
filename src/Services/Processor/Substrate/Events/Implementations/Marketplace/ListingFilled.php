<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace;

use Carbon\Carbon;
use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\Marketplace\Events\Substrate\Marketplace\ListingFilled as ListingFilledEvent;
use Enjin\Platform\Marketplace\Models\MarketplaceSale;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Traits\QueryDataOrFail;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Marketplace\ListingFilled as ListingFilledPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;
use Enjin\Platform\Support\Account;
use Facades\Enjin\Platform\Services\Database\WalletService;
use Illuminate\Support\Facades\Log;

class ListingFilled implements SubstrateEvent
{
    use QueryDataOrFail;

    /**
     * Handles the listing filled event.
     */
    public function run(PolkadartEvent $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof ListingFilledPolkadart) {
            return;
        }

        $listingId = HexConverter::prefix($event->listingId);
        $listing = $this->getListing($listingId);
        $buyer = WalletService::firstOrStore(['account' => Account::parseAccount($event->buyer)]);

        $sale = MarketplaceSale::create([
            'marketplace_listing_id' => $listing->id,
            'listing_id' => $listing->listing_id,
            'wallet_id' => $buyer->id,
            'price' => $listing->price,
            'amount' => $event->amountFilled,
            'created_at' => $now = Carbon::now(),
            'updated_at' => $now,
        ]);

        Log::info(
            sprintf(
                'Listing %s (id: %s) was filled with %s amount from sale (id: %s) from %s (id: %s).',
                $listingId,
                $listing->id,
                $event->amountFilled,
                $sale->id,
                $event->buyer,
                $buyer->id,
            )
        );

        ListingFilledEvent::safeBroadcast($listing, $sale);
    }
}
