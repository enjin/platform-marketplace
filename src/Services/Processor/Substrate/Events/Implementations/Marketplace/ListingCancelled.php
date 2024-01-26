<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace;

use Carbon\Carbon;
use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\Marketplace\Enums\ListingState;
use Enjin\Platform\Marketplace\Events\Substrate\Marketplace\ListingCancelled as ListingCancelledEvent;
use Enjin\Platform\Marketplace\Models\MarketplaceState;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Traits\QueryDataOrFail;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Models\Transaction;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Marketplace\ListingCancelled as ListingCancelledPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;
use Illuminate\Support\Facades\Log;

class ListingCancelled implements SubstrateEvent
{
    use QueryDataOrFail;

    /**
     * Handles the listing cancelled event.
     */
    public function run(PolkadartEvent $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof ListingCancelledPolkadart) {
            return;
        }

        $listingId = HexConverter::prefix($event->listingId);

        try {
            $listing = $this->getListing($listingId);

            $state = MarketplaceState::create([
                'marketplace_listing_id' => $listing->id,
                'state' => ListingState::CANCELLED->name,
                'height' => $block->number,
                'created_at' => $now = Carbon::now(),
                'updated_at' => $now,
            ]);

            Log::info(
                sprintf(
                    'Listing %s (id: %s) was cancelled (id: %s).',
                    $listingId,
                    $listing->id,
                    $state->id,
                )
            );

            $extrinsic = $block->extrinsics[$event->extrinsicIndex];
            ListingCancelledEvent::safeBroadcast(
                $listing,
                $state,
                Transaction::firstWhere(['transaction_chain_hash' => $extrinsic->hash])
            );
        } catch (\Throwable $e) {
            Log::error(
                sprintf(
                    'Listing %s was cancelled but could not be found in the database.',
                    $listingId,
                )
            );
        }
    }
}
