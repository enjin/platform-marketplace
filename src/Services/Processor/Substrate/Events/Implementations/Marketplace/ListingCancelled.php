<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace;

use Carbon\Carbon;
use Enjin\Platform\Marketplace\Enums\ListingState;
use Enjin\Platform\Marketplace\Events\Substrate\Marketplace\ListingCancelled as ListingCancelledEvent;
use Enjin\Platform\Marketplace\Models\Laravel\Wallet;
use Enjin\Platform\Marketplace\Models\MarketplaceState;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\MarketplaceSubstrateEvent;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Marketplace\ListingCancelled as ListingCancelledPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Facades\Log;

class ListingCancelled extends MarketplaceSubstrateEvent
{
    /** @var ListingCancelledPolkadart */
    protected Event $event;

    /**
     * Handles the listing cancelled event.
     */
    public function run(Event $event, \Enjin\Platform\Models\Laravel\Block $block, \Enjin\Platform\Services\Processor\Substrate\Codec\Codec $codec): void
    {
        try {
            // Fails if the listing is not found
            $listing = $this->getListing($this->event->listingId);
            $seller = Wallet::find($listing->seller_wallet_id);

            MarketplaceState::create([
                'marketplace_listing_id' => $listing->id,
                'state' => ListingState::CANCELLED->name,
                'height' => $this->block->number,
                'created_at' => $now = Carbon::now(),
                'updated_at' => $now,
            ]);

            $this->extra = [
                'collection_id' => $listing->make_collection_chain_id,
                'token_id' => $listing->make_token_chain_id,
                'seller' => $seller->public_key,
            ];
        } catch (\Throwable) {
            Log::error(
                sprintf(
                    'Listing %s was cancelled but could not be found in the database.',
                    $this->event->listingId,
                )
            );
        }
    }

    public function log(): void
    {
        Log::debug(
            sprintf(
                'Listing %s was cancelled.',
                $this->event->listingId,
            )
        );
    }

    public function broadcast(): void
    {
        ListingCancelledEvent::safeBroadcast(
            $this->event,
            $this->getTransaction($this->block, $this->event->extrinsicIndex),
            $this->extra,
        );
    }
}
