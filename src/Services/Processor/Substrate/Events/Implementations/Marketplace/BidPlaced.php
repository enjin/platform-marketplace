<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace;

use Carbon\Carbon;
use Enjin\Platform\Marketplace\Events\Substrate\Marketplace\BidPlaced as BidPlacedEvent;
use Enjin\Platform\Marketplace\Models\MarketplaceBid;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\MarketplaceSubstrateEvent;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Marketplace\BidPlaced as BidPlacedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Facades\Log;

class BidPlaced extends MarketplaceSubstrateEvent
{
    /**
     * Handles the bid placed event.
     */
    public function run(Event $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof BidPlacedPolkadart) {
            return;
        }

        try {
            $listing = $this->getListing($event->listingId);
            $bidder = $this->firstOrStoreAccount($event->bidder);

            $bid = MarketplaceBid::create([
                'marketplace_listing_id' => $listing->id,
                'wallet_id' => $bidder->id,
                'price' => $event->price,
                'height' => $block->number,
                'created_at' => $now = Carbon::now(),
                'updated_at' => $now,
            ]);

            Log::info(
                sprintf(
                    '%s (id: %s) placed a bid (id: %s) on listing %s (id: %s).',
                    $event->bidder,
                    $bidder->id,
                    $bid->id,
                    $event->listingId,
                    $listing->id,
                )
            );

            BidPlacedEvent::safeBroadcast(
                $listing,
                $bid,
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
