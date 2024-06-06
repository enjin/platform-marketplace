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
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Marketplace\ListingCreated as ListingCreatedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ListingCreated extends MarketplaceSubstrateEvent
{
    /** @var ListingCreatedPolkadart */
    protected Event $event;

    protected MarketplaceListing $listingCreated;

    /**
     * Handles the listing created event.
     */
    public function run(): void
    {
        if (!$this->shouldSyncCollection(Arr::get($this->event->makeAssetId, 'collection_id'))
            && !$this->shouldSyncCollection(Arr::get($this->event->takeAssetId, 'collection_id'))
        ) {
            return;
        }
        $seller = $this->firstOrStoreAccount($this->event->seller);
        $this->listingCreated = MarketplaceListing::updateOrCreate([
            'listing_chain_id' => $this->event->listingId,
        ], [
            'seller_wallet_id' => $seller->id,
            'make_collection_chain_id' => Arr::get($this->event->makeAssetId, 'collection_id'),
            'make_token_chain_id' => Arr::get($this->event->makeAssetId, 'token_id'),
            'take_collection_chain_id' => Arr::get($this->event->takeAssetId, 'collection_id'),
            'take_token_chain_id' => Arr::get($this->event->takeAssetId, 'token_id'),
            'amount' => $this->event->amount,
            'price' => $this->event->price,
            'min_take_value' => $this->event->minTakeValue,
            'fee_side' => FeeSide::tryFrom($this->event->feeSide)?->name,
            'creation_block' => $this->event->creationBlock,
            'deposit' => $this->event->deposit,
            'salt' => $this->event->salt,
            'type' => ListingType::from(array_key_first($this->event->state))->name,
            'start_block' => Arr::get($this->event->data, 'Auction.start_block'),
            'end_block' => Arr::get($this->event->data, 'Auction.end_block'),
            'amount_filled' => $this->getValue($this->event->state, ['FixedPrice.amount_filled', 'FixedPrice']),
            'created_at' => $now = Carbon::now(),
            'updated_at' => $now,
        ]);

        MarketplaceState::create([
            'marketplace_listing_id' => $this->listingCreated->id,
            'state' => ListingState::ACTIVE->name,
            'height' => $this->event->creationBlock,
            'created_at' => $now = Carbon::now(),
            'updated_at' => $now,
        ]);

        $this->extra = [
            'collection_id' => $this->listingCreated->make_collection_chain_id,
            'token_id' => $this->listingCreated->make_token_chain_id,
            'seller' => $seller->public_key,
        ];
    }

    public function log(): void
    {
        Log::debug(
            sprintf(
                'Listing %s was created.',
                $this->event->listingId,
            )
        );
    }

    public function broadcast(): void
    {
        ListingCreatedEvent::safeBroadcast(
            $this->event,
            $this->getTransaction($this->block, $this->event->extrinsicIndex),
            $this->extra,
            $this->listingCreated,
        );
    }
}
