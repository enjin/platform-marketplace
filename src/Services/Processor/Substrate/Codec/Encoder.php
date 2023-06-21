<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Codec;

use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\Marketplace\Models\Substrate\AuctionDataParams;
use Enjin\Platform\Marketplace\Models\Substrate\MultiTokensTokenAssetIdParams;
use Enjin\Platform\Services\Processor\Substrate\Codec\Encoder as BaseEncoder;

class Encoder extends BaseEncoder
{
    /**
     * Create a listing on the marketplace.
     */
    public function createListing(
        MultiTokensTokenAssetIdParams $makeAsset,
        MultiTokensTokenAssetIdParams $takeAsset,
        string $amount,
        string $price,
        string $salt,
        ?AuctionDataParams $data = null
    ): string {
        $encoded = $this->scaleInstance->createTypeByTypeString('CreateListing')->encode([
            'callIndex' => $this->getCallIndex('Marketplace.create_listing'),
            'makeAssetId' => $makeAsset->toEncodable(),
            'takeAssetId' => $takeAsset->toEncodable(),
            'amount' => gmp_init($amount),
            'price' => gmp_init($price),
            'salt' => HexConverter::stringToHexPrefixed($salt),
            'auctionData' => $data?->toEncodable(),
        ]);

        return HexConverter::prefix($encoded);
    }

    /**
     * Cancel a listing on the marketplace.
     */
    public function cancelListing(string $listingId): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('CancelListing')->encode([
            'callIndex' => $this->getCallIndex('Marketplace.cancel_listing'),
            'listingId' => HexConverter::unPrefix($listingId),
        ]);

        return HexConverter::prefix($encoded);
    }

    /**
     * Fills a fixed price listing.
     */
    public function fillListing(string $listingId, string $amount): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('FillListing')->encode([
            'callIndex' => $this->getCallIndex('Marketplace.fill_listing'),
            'listingId' => HexConverter::unPrefix($listingId),
            'amount' => gmp_init($amount),
        ]);

        return HexConverter::prefix($encoded);
    }

    /**
     * Finalize the auction.
     */
    public function finalizeAuction(string $listingId): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('FinalizeAuction')->encode([
            'callIndex' => $this->getCallIndex('Marketplace.finalize_auction'),
            'listingId' => HexConverter::unPrefix($listingId),
        ]);

        return HexConverter::prefix($encoded);
    }

    /**
     * Place a bid on a listng.
     */
    public function placeBid(string $listingId, string $price): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('PlaceBid')->encode([
            'callIndex' => $this->getCallIndex('Marketplace.place_bid'),
            'listingId' => HexConverter::unPrefix($listingId),
            'price' => gmp_init($price),
        ]);

        return HexConverter::prefix($encoded);
    }

    /**
     * Change the protocol fee.
     */
    public function setProtocolFee(string $fee): string
    {
        $encoded = $this->scaleInstance->createTypeByTypeString('SetProtocolFee')->encode([
            'callIndex' => $this->getCallIndex('Marketplace.set_protocol_fee'),
            'fee' => gmp_init($fee),
        ]);

        return HexConverter::prefix($encoded);
    }
}
