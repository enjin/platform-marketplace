<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace;

use Carbon\Carbon;
use Enjin\Platform\Marketplace\Enums\ListingState;
use Enjin\Platform\Marketplace\Events\Substrate\Marketplace\ListingCancelled as ListingCancelledEvent;
use Enjin\Platform\Marketplace\Models\MarketplaceState;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\MarketplaceSubstrateEvent;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Marketplace\ListingCancelled as ListingCancelledPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Event;
use Illuminate\Support\Facades\Log;

class ListingCancelled extends MarketplaceSubstrateEvent
{
    /**
     * Handles the listing cancelled event.
     */
    public function run(Event $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof ListingCancelledPolkadart) {
            return;
        }


        ray($event);

        throw new \Exception('AuctionFinalized');

        try {
            $listing = $this->getListing($event->listingId);

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
                    $event->listingId,
                    $listing->id,
                    $state->id,
                )
            );

            ListingCancelledEvent::safeBroadcast(
                $listing,
                $state,
                $this->getTransaction($block, $event->extrinsicIndex),
            );
        } catch (\Throwable $e) {
            Log::error(
                sprintf(
                    'Listing %s was cancelled but could not be found in the database.',
                    $event->listingId,
                )
            );
        }
    }
}
