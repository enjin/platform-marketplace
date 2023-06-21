<?php

namespace Enjin\Platform\Marketplace\Services;

use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Traits\HasEncodableTokenId;
use Enjin\Platform\Marketplace\Models\Substrate\AuctionDataParams;
use Enjin\Platform\Marketplace\Models\Substrate\MultiTokensTokenAssetIdParams;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Services\Database\TransactionService as DatabaseTransactionService;
use Enjin\Platform\Services\Database\WalletService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TransactionService extends DatabaseTransactionService
{
    use HasEncodableTokenId;

    /**
     * Create a new service instance.
     */
    public function __construct(
        public readonly Codec $codec,
        public readonly WalletService $wallet
    ) {
    }

    /**
     * Create a listing on the marketplace.
     */
    public function createListing(array $args): Model
    {
        return $this->store(
            [
                'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
                'encoded_data' => $this->codec->encode()->createListing(
                    new MultiTokensTokenAssetIdParams(
                        Arr::get($args, 'makeAssetId.collectionId'),
                        $this->encodeTokenId(Arr::get($args, 'makeAssetId'))
                    ),
                    new MultiTokensTokenAssetIdParams(
                        Arr::get($args, 'takeAssetId.collectionId'),
                        $this->encodeTokenId(Arr::get($args, 'takeAssetId'))
                    ),
                    Arr::get($args, 'amount'),
                    Arr::get($args, 'price'),
                    Arr::get($args, 'salt', Str::random(10)),
                    ($data = Arr::get($args, 'auctionData'))
                        ? new AuctionDataParams(Arr::get($data, 'startBlock'), Arr::get($data, 'endBlock'))
                        : null
                ),
                'method' => 'CreateListing',
            ]
        );
    }

    /**
     * Cancel a listing on the marketplace.
     */
    public function cancelListing(array $args): Model
    {
        return $this->store(
            [
                'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
                'encoded_data' => $this->codec->encode()->cancelListing(Arr::get($args, 'listingId')),
                'method' => 'CancelListing',
            ]
        );
    }

    /**
     * Fills a fixed price listing.
     */
    public function fillListing(array $args): Model
    {
        return $this->store(
            [
                'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
                'encoded_data' => $this->codec->encode()->fillListing(
                    Arr::get($args, 'listingId'),
                    Arr::get($args, 'amount')
                ),
                'method' => 'FillListing',
            ]
        );
    }

    /**
     * Finalize the auction.
     */
    public function finalizeAuction(array $args): Model
    {
        return $this->store(
            [
                'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
                'encoded_data' => $this->codec->encode()->finalizeAuction(Arr::get($args, 'listingId')),
                'method' => 'FinalizeAuction',
            ]
        );
    }

    /**
     * Place a bid on a listng.
     */
    public function placeBid(array $args): Model
    {
        return $this->store(
            [
                'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
                'encoded_data' => $this->codec->encode()->placeBid(
                    Arr::get($args, 'listingId'),
                    Arr::get($args, 'price'),
                ),
                'method' => 'PlaceBid',
            ]
        );
    }

    /**
     * Change the protocol fee.
     */
    public function setProtocolFee(array $args): Model
    {
        return $this->store(
            [
                'idempotency_key' => Arr::get($args, 'idempotency_key', Str::uuid()->toString()),
                'encoded_data' => $this->codec->encode()->setProtocolFee(Arr::get($args, 'fee'), ),
                'method' => 'SetProtocolFee',
            ]
        );
    }
}
