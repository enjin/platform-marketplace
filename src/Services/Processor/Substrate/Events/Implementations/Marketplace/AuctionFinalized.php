<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace;

use Carbon\Carbon;
use Enjin\Platform\Marketplace\Enums\ListingState;
use Enjin\Platform\Marketplace\Events\Substrate\Marketplace\AuctionFinalized as AuctionFinalizedEvent;
use Enjin\Platform\Marketplace\Models\MarketplaceSale;
use Enjin\Platform\Marketplace\Models\MarketplaceState;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\MarketplaceSubstrateEvent;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Marketplace\AuctionFinalized as AuctionFinalizedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Facades\Log;

class AuctionFinalized extends MarketplaceSubstrateEvent
{
    /**
     * Handles the auction finalized event.
     */
    public function run(Event $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof AuctionFinalizedPolkadart) {
            return;
        }

        try {
            $listing = $this->getListing($event->listingId);
            $bidder = $this->firstOrStoreAccount($event->winningBidder);

            $state = MarketplaceState::create([
                'marketplace_listing_id' => $listing->id,
                'state' => ListingState::FINALIZED->name,
                'height' => $block->number,
                'created_at' => $now = Carbon::now(),
                'updated_at' => $now,
            ]);

            $sale = MarketplaceSale::create([
                'listing_chain_id' => $listing->listing_chain_id,
                'wallet_id' => $bidder->id,
                'price' => $event->price,
                'amount' => $listing->amount,
            ]);

            Log::info(
                sprintf(
                    'Listing %s (id: %s) was finalized (id: %s) with a sale (id: %s) from %s (id: %s).',
                    $event->listingId,
                    $listing->id,
                    $state->id,
                    $sale->id,
                    $event->winningBidder,
                    $bidder->id,
                )
            );

            AuctionFinalizedEvent::safeBroadcast(
                $listing,
                $state,
                $sale,
                $this->getTransaction($block, $event->extrinsicIndex),
            );
        } catch (\Throwable $e) {
            Log::error(
                sprintf(
                    'Listing %s was finalized but could not be found in the database.',
                    $event->listingId,
                )
            );
        }
    }
}
