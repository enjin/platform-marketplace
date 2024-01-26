<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace;

use Carbon\Carbon;
use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\Marketplace\Events\Substrate\Marketplace\BidPlaced as BidPlacedEvent;
use Enjin\Platform\Marketplace\Models\MarketplaceBid;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Traits\QueryDataOrFail;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Models\Transaction;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Marketplace\BidPlaced as BidPlacedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;
use Enjin\Platform\Support\Account;
use Facades\Enjin\Platform\Services\Database\WalletService;
use Illuminate\Support\Facades\Log;

class BidPlaced implements SubstrateEvent
{
    use QueryDataOrFail;

    /**
     * Handles the bid placed event.
     */
    public function run(PolkadartEvent $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof BidPlacedPolkadart) {
            return;
        }

        $listingId = HexConverter::prefix($event->listingId);

        try {
            $listing = $this->getListing($listingId);
            $bidder = WalletService::firstOrStore(['account' => Account::parseAccount($event->bidder)]);

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
                    $listingId,
                    $listing->id,
                )
            );

            $extrinsic = $block->extrinsics[$event->extrinsicIndex];
            BidPlacedEvent::safeBroadcast(
                $listing,
                $bid,
                Transaction::firstWhere(['transaction_chain_hash' => $extrinsic->hash])
            );
        } catch (\Throwable $e) {
            Log::error(
                sprintf(
                    'Listing %s was filled but could not be found in the database.',
                    $listingId,
                )
            );
        }
    }
}
