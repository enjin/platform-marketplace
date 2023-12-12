<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace;

use Carbon\Carbon;
use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\Marketplace\Enums\FeeSide;
use Enjin\Platform\Marketplace\Enums\ListingState;
use Enjin\Platform\Marketplace\Enums\ListingType;
use Enjin\Platform\Marketplace\Events\Substrate\Marketplace\ListingCreated as ListingCreatedEvent;
use Enjin\Platform\Marketplace\Models\MarketplaceListing;
use Enjin\Platform\Marketplace\Models\MarketplaceState;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Traits\QueryDataOrFail;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Marketplace\ListingCreated as ListingCreatedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;
use Enjin\Platform\Support\Account;
use Facades\Enjin\Platform\Services\Database\WalletService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ListingCreated implements SubstrateEvent
{
    use QueryDataOrFail;

    /**
     * Handles the listing created event.
     */
    public function run(PolkadartEvent $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof ListingCreatedPolkadart) {
            return;
        }

        $listingId = HexConverter::prefix($event->listingId);
        $seller = WalletService::firstOrStore(['account' => Account::parseAccount($event->seller)]);

        $listing = MarketplaceListing::create([
            'listing_id' => $listingId,
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
            'salt' => HexConverter::bytesToHex($event->salt),
            'type' => is_string($event->data) ? ListingType::FIXED_PRICE->name : ListingType::AUCTION->name,
            'start_block' => Arr::get($event->data, 'Auction.start_block'),
            'end_block' => Arr::get($event->data, 'Auction.end_block'),
            'amount_filled' => Arr::get($event->state, 'FixedPrice.amount_filled'),
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
                $listingId,
                $listing->id,
            )
        );

        ListingCreatedEvent::safeBroadcast($listing, $state);
    }
}
