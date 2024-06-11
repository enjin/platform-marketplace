<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace;

use Carbon\Carbon;
use Enjin\Platform\Marketplace\Enums\ListingState;
use Enjin\Platform\Marketplace\Events\Substrate\Marketplace\AuctionFinalized as AuctionFinalizedEvent;
use Enjin\Platform\Marketplace\Models\Laravel\Wallet;
use Enjin\Platform\Marketplace\Models\MarketplaceSale;
use Enjin\Platform\Marketplace\Models\MarketplaceState;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\MarketplaceSubstrateEvent;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Marketplace\AuctionFinalized as AuctionFinalizedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Facades\Log;

class AuctionFinalized extends MarketplaceSubstrateEvent
{
    /** @var AuctionFinalizedPolkadart */
    protected Event $event;

    /**
     * Handles the auction finalized event.
     */
    public function run(): void
    {
        try {
            // Fails if the listing is not found
            $listing = $this->getListing($this->event->listingId);
            $bidder = $this->firstOrStoreAccount($this->event->winningBidder);
            $seller = Wallet::find($listing->seller_wallet_id);

            MarketplaceState::create([
                'marketplace_listing_id' => $listing->id,
                'state' => ListingState::FINALIZED->name,
                'height' => $this->block->number,
                'created_at' => $now = Carbon::now(),
                'updated_at' => $now,
            ]);

            MarketplaceSale::create([
                'listing_chain_id' => $listing->listing_chain_id,
                'wallet_id' => $bidder->id,
                'price' => $this->event->price,
                'amount' => $listing->amount,
            ]);

            $this->extra = [
                'collection_id' => $listing->make_collection_chain_id,
                'token_id' => $listing->make_token_chain_id,
                'bidder' => $bidder->public_key,
                'seller' => $seller->public_key,
            ];
        } catch (\Throwable) {
            Log::error(
                sprintf(
                    'Listing %s was finalized but could not be found in the database.',
                    $this->event->listingId,
                )
            );
        }
    }

    public function log(): void
    {
        Log::debug(
            sprintf(
                'Listing %s was finalized with a sale from %s.',
                $this->event->listingId,
                $this->event->winningBidder,
            )
        );

    }

    public function broadcast(): void
    {
        AuctionFinalizedEvent::safeBroadcast(
            $this->event,
            $this->getTransaction($this->block, $this->event->extrinsicIndex),
            $this->extra,
        );
    }
}
