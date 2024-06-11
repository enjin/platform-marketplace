<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace;

use Carbon\Carbon;
use Enjin\Platform\Marketplace\Events\Substrate\Marketplace\ListingFilled as ListingFilledEvent;
use Enjin\Platform\Marketplace\Models\MarketplaceSale;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\MarketplaceSubstrateEvent;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Marketplace\ListingFilled as ListingFilledPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Facades\Log;

class ListingFilled extends MarketplaceSubstrateEvent
{
    /**
     * Handles the listing filled event.
     */
    public function run(Event $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof ListingFilledPolkadart) {
            return;
        }

        try {
            $listing = $this->getListing($event->listingId);
            $buyer = $this->firstOrStoreAccount($event->buyer);

            $sale = MarketplaceSale::create([
                'listing_chain_id' => $listing->listing_chain_id,
                'wallet_id' => $buyer->id,
                'price' => $listing->price,
                'amount' => $event->amountFilled,
                'created_at' => $now = Carbon::now(),
                'updated_at' => $now,
            ]);

            Log::info(
                sprintf(
                    'Listing %s (id: %s) was filled with %s amount from sale (id: %s) from %s (id: %s).',
                    $event->listingId,
                    $listing->id,
                    $event->amountFilled,
                    $sale->id,
                    $event->buyer,
                    $buyer->id,
                )
            );

            ListingFilledEvent::safeBroadcast(
                $listing,
                $sale,
                $this->getTransaction($block, $event->extrinsicIndex),
            );
        } catch (\Throwable $e) {
            Log::error(
                sprintf(
                    'Listing %s was filled but could not be found in the database.',
                    $event->listingId,
                )
            );
        }
    }
}
