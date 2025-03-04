<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate;

use Closure;
use Enjin\Platform\Marketplace\Enums\ListingState;
use Enjin\Platform\Marketplace\Enums\ListingType;
use Enjin\Platform\Marketplace\Models\MarketplaceBid;
use Enjin\Platform\Marketplace\Models\MarketplaceListing;
use Enjin\Platform\Marketplace\Models\MarketplaceState;
use Enjin\Platform\Marketplace\Services\MarketplaceService;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Processor\Substrate\Parser as BaseParser;
use Illuminate\Support\Arr;

class Parser extends BaseParser
{
    protected static $listingCache = [];
    protected Codec $codec;

    /**
     * Creates the parser instance.
     */
    public function __construct(protected MarketplaceService $marketplaceService)
    {
        parent::__construct();
        $this->codec = new Codec();
    }

    /**
     * Parses the listing storage data.
     */
    public function listingsStorages(array $data): void
    {
        $insertData = [];
        $insertBids = [];
        $insertStates = [];

        foreach ($data as [$key, $listing]) {
            $listingKey = $this->codec->decoder()->listingStorageKey($key);
            $listingData = $this->codec->decoder()->listingStorageData($listing);

            $collectionFilter = config('enjin-platform.indexing.filters.collections');

            if (!empty($collectionFilter)) {
                $shouldParse = in_array([
                    Arr::get($listingData, 'makeAssetId')->collectionId,
                    Arr::get($listingData, 'takeAssetId')->collectionId,
                ], $collectionFilter);

                if (!$shouldParse) {
                    continue;
                }
            }

            $sellerWallet = $this->getCachedWallet(
                $user = $listingData['seller'],
                fn () => $this->walletService->firstOrStore(['account' => $user])
            );

            if (!empty($bidder = Arr::get($listingData, 'state.Auction.highBid.bidder'))) {
                $insertBids[] = [
                    'listingId' => Arr::get($listingKey, 'listingId'),
                    'listingData' => $listingData,
                    'bidder' => $bidder,
                ];
            }

            $insertStates[] = [
                'listingId' => Arr::get($listingKey, 'listingId'),
                'state' => Arr::get($listingData, 'state'),
                'height' => $creationBlock = Arr::get($listingData, 'creationBlock'),
            ];

            $insertData[] = [
                'listing_chain_id' => Arr::get($listingKey, 'listingId'),
                'seller_wallet_id' => $sellerWallet->id,
                'make_collection_chain_id' => Arr::get($listingData, 'makeAssetId')->collectionId, // Shouldn't we use the primary keys?
                'make_token_chain_id' => Arr::get($listingData, 'makeAssetId')->tokenId, // Shouldn't we use the primary keys?
                'take_collection_chain_id' => Arr::get($listingData, 'takeAssetId')->collectionId, // Shouldn't we use the primary keys?
                'take_token_chain_id' => Arr::get($listingData, 'takeAssetId')->tokenId, // Shouldn't we use the primary keys?
                'amount' => Arr::get($listingData, 'amount'),
                'price' => Arr::get($listingData, 'price'),
                'min_take_value' => Arr::get($listingData, 'minTakeValue'),
                'fee_side' => Arr::get($listingData, 'feeSide')->name,
                'creation_block' => $creationBlock,
                'deposit' => Arr::get($listingData, 'deposit'),
                'salt' => Arr::get($listingData, 'salt'),
                'type' => ListingType::tryFrom(array_key_first(Arr::get($listingData, 'data')))?->name,
                'auction_start_block' => Arr::get($listingData, 'data.Auction.startBlock'),
                'auction_end_block' => Arr::get($listingData, 'data.Auction.endBlock'),
                'offer_expiration' => Arr::get($listingData, 'data.Offer.expiration'),
                'counter_offer_count' => ($counterOfferCount = Arr::get($listingData, 'state.Offer.counterOfferCount')) !== null ? gmp_strval($counterOfferCount) : null,
                'amount_filled' => ($amountFilled = Arr::get($listingData, 'state.FixedPrice.amountFilled')) !== null ? gmp_strval($amountFilled) : null,
            ];
        }

        MarketplaceListing::upsert($insertData, uniqueBy: 'listing_chain_id');

        $this->marketplaceBids($insertBids);
        $this->marketplaceStates($insertStates);
    }

    /**
     * Parses the marketplace states.
     */
    protected function marketplaceStates(array $data): void
    {
        if (empty($data)) {
            return;
        }

        $insertStates = [];
        foreach ($data as $state) {
            $listing = $this->getCachedListing(
                $listingId = Arr::get($state, 'listingId'),
                fn () => $this->marketplaceService->get($listingId),
            );

            $insertStates[] = [
                'marketplace_listing_id' => $listing->id,
                // If the listing is currently in the storage means it is active
                'state' => ListingState::ACTIVE->name,
                'height' => Arr::get($state, 'height'),
            ];
        }

        MarketplaceState::insert($insertStates);
    }

    /**
     * Parses the marketplace bids.
     */
    protected function marketplaceBids(array $data): void
    {
        if (empty($data)) {
            return;
        }

        $insertBids = [];
        foreach ($data as $bid) {
            $listing = $this->getCachedListing(
                $listingId = Arr::get($bid, 'listingId'),
                fn () => $this->marketplaceService->get($listingId),
            );

            $bidderWallet = $this->getCachedWallet(
                $user = Arr::get($bid, 'bidder'),
                fn () => $this->walletService->firstOrStore(['account' => $user])
            );

            $insertBids[] = [
                'marketplace_listing_id' => $listing->id,
                'wallet_id' => $bidderWallet->id,
                'price' => ($price = Arr::get($bid, 'listingData.state.Auction.highBid.price')) !== null ? gmp_strval($price) : null,
                // There is no way to know this info from state we will default to auction started block
                'height' => Arr::get($bid, 'listingData.creationBlock'),
            ];
        }

        MarketplaceBid::insert($insertBids);
    }

    /**
     * Get the cached listing data.
     */
    protected function getCachedListing(string $key, ?Closure $default = null): mixed
    {
        if (!isset(static::$listingCache[$key])) {
            static::$listingCache[$key] = $default();
        }

        return static::$listingCache[$key];
    }
}
