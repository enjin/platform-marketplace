<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Marketplace;

use Carbon\Carbon;
use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\Marketplace\Enums\ListingState;
use Enjin\Platform\Marketplace\Events\Substrate\Marketplace\AuctionFinalized as AuctionFinalizedEvent;
use Enjin\Platform\Marketplace\Models\MarketplaceSale;
use Enjin\Platform\Marketplace\Models\MarketplaceState;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Traits\QueryDataOrFail;
use Enjin\Platform\Models\Laravel\Block;
use Enjin\Platform\Models\Transaction;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\Events\Marketplace\AuctionFinalized as AuctionFinalizedPolkadart;
use Enjin\Platform\Services\Processor\Substrate\Codec\Polkadart\PolkadartEvent;
use Enjin\Platform\Services\Processor\Substrate\Events\SubstrateEvent;
use Enjin\Platform\Support\Account;
use Facades\Enjin\Platform\Services\Database\WalletService;
use Illuminate\Support\Facades\Log;

class AuctionFinalized implements SubstrateEvent
{
    use QueryDataOrFail;

    /**
     * Handles the auction finalized event.
     */
    public function run(PolkadartEvent $event, Block $block, Codec $codec): void
    {
        if (!$event instanceof AuctionFinalizedPolkadart) {
            return;
        }

        $listingId = HexConverter::prefix($event->listingId);

        try {
            $listing = $this->getListing($listingId);
            $bidder = WalletService::firstOrStore(['account' => Account::parseAccount($event->winningBidder)]);

            $state = MarketplaceState::create([
                'marketplace_listing_id' => $listing->id,
                'state' => ListingState::FINALIZED->name,
                'height' => $block->number,
                'created_at' => $now = Carbon::now(),
                'updated_at' => $now,
            ]);

            $sale = MarketplaceSale::create([
                'marketplace_listing_id' => $listing->id,
                'listing_chain_id' => $listing->listing_chain_id,
                'wallet_id' => $bidder->id,
                'price' => $event->price,
                'amount' => $listing->amount,
            ]);

            Log::info(
                sprintf(
                    'Listing %s (id: %s) was finalized (id: %s) with a sale (id: %s) from %s (id: %s).',
                    $listingId,
                    $listing->id,
                    $state->id,
                    $sale->id,
                    $event->winningBidder,
                    $bidder->id,
                )
            );

            $extrinsic = $block->extrinsics[$event->extrinsicIndex];
            AuctionFinalizedEvent::safeBroadcast(
                $listing,
                $state,
                $sale,
                Transaction::firstWhere(['transaction_chain_hash' => $extrinsic->hash])
            );
        } catch (\Throwable $e) {
            Log::error(
                sprintf(
                    'Listing %s was finalized but could not be found in the database.',
                    $listingId,
                )
            );
        }
    }
}
