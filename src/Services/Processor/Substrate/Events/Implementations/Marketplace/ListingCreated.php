<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace;

use Carbon\Carbon;
use Enjin\Platform\Marketplace\Enums\FeeSide;
use Enjin\Platform\Marketplace\Enums\ListingState;
use Enjin\Platform\Marketplace\Enums\ListingType;
use Enjin\Platform\Marketplace\Events\Substrate\Marketplace\ListingCreated as ListingCreatedEvent;
use Enjin\Platform\Marketplace\Models\MarketplaceListing;
use Enjin\Platform\Marketplace\Models\MarketplaceState;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\MarketplaceSubstrateEvent;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Marketplace\ListingCreated as ListingCreatedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ListingCreated extends MarketplaceSubstrateEvent
{
    /**
     * Handles the listing created event.
     */
    public function run(Event $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof ListingCreatedPolkadart) {
            return;
        }

        if (!$this->shouldSyncCollection(Arr::get($event->makeAssetId, 'collection_id')) && !$this->shouldSyncCollection(Arr::get($event->takeAssetId, 'collection_id'))) {
            return;
        }

        $seller = $this->firstOrStoreAccount($event->seller);
        $listing = MarketplaceListing::updateOrCreate([
            'listing_chain_id' => $event->listingId,
        ], [
            'seller_wallet_id' => $seller->id,
            'make_collection_chain_id' => Arr::get($event->makeAssetId, 'collection_id'),
            'make_token_chain_id' => Arr::get($event->makeAssetId, 'token_id'),
            'take_collection_chain_id' => Arr::get($event->takeAssetId, 'collection_id'),
            'take_token_chain_id' => Arr::get($event->takeAssetId, 'token_id'),
            'amount' => $event->amount,
            'price' => $event->price,
            'min_take_value' => $event->minTakeValue,
            'fee_side' => FeeSide::tryFrom($event->feeSide)?->name,
            'creation_block' => $event->creationBlock,
            'deposit' => $event->deposit,
            'salt' => $event->salt,
            'type' => ListingType::from(array_key_first($event->state))->name,
            'start_block' => Arr::get($event->data, 'Auction.start_block'),
            'end_block' => Arr::get($event->data, 'Auction.end_block'),
            'amount_filled' => $this->getValue($event->state, ['FixedPrice.amount_filled', 'FixedPrice']),
            'created_at' => $now = Carbon::now(),
            'updated_at' => $now,
        ]);

        $state = MarketplaceState::create([
            'marketplace_listing_id' => $listing->id,
            'state' => ListingState::ACTIVE->name,
            'height' => $event->creationBlock,
            'created_at' => $now = Carbon::now(),
            'updated_at' => $now,
        ]);

        Log::info(
            sprintf(
                'Listing %s (id: %s) was created.',
                $event->listingId,
                $listing->id,
            )
        );

        ListingCreatedEvent::safeBroadcast(
            $listing,
            $state,
            $this->getTransaction($block, $event->extrinsicIndex),
        );
    }
}
