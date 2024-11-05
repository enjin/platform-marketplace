<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace;

use Carbon\Carbon;
use Enjin\Platform\Marketplace\Events\Substrate\Marketplace\ListingFilled as ListingFilledEvent;
use Enjin\Platform\Marketplace\Models\Laravel\Wallet;
use Enjin\Platform\Marketplace\Models\MarketplaceSale;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\MarketplaceSubstrateEvent;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Marketplace\ListingFilled as ListingFilledPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Facades\Log;

class ListingFilled extends MarketplaceSubstrateEvent
{
    /** @var ListingFilledPolkadart */
    protected Event $event;

    /**
     * Handles the listing filled event.
     */
    public function run(Event $event, \Enjin\Platform\Models\Laravel\Block $block, \Enjin\Platform\Services\Processor\Substrate\Codec\Codec $codec): void
    {
        try {
            // Fails to get the listing from the database
            $listing = $this->getListing($this->event->listingId);
            $seller = Wallet::find($listing->seller_wallet_id);
            $buyer = $this->firstOrStoreAccount($this->event->buyer);

            MarketplaceSale::create([
                'listing_chain_id' => $listing->listing_chain_id,
                'wallet_id' => $buyer->id,
                'price' => $listing->price,
                'amount' => $this->event->amountFilled,
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
                    'Listing %s was filled but could not be found in the database.',
                    $this->event->listingId,
                )
            );
        }
    }

    public function log(): void
    {
        Log::debug(
            sprintf(
                'Listing %s was filled with %s amount from %s.',
                $this->event->listingId,
                $this->event->amountFilled,
                $this->event->buyer,
            )
        );
    }

    public function broadcast(): void
    {
        ListingFilledEvent::safeBroadcast(
            $this->event,
            $this->getTransaction($this->block, $this->event->extrinsicIndex),
            $this->extra,
        );
    }
}
