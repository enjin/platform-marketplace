<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Codec;

use Codec\ScaleBytes;
use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\Marketplace\Enums\FeeSide;
use Enjin\Platform\Marketplace\Models\Substrate\MultiTokensTokenAssetIdParams;
use Enjin\Platform\Services\Processor\Substrate\Codec\Decoder as BaseDecoder;
use Illuminate\Support\Arr;

class Decoder extends BaseDecoder
{
    /**
     * Returns the decoded listing storage key.
     */
    public function listingStorageKey(string $data): array
    {
        $decoded = $this->codec->process('ListingStorageKey', new ScaleBytes($data));

        return [
            'listingId' => ($listingId = Arr::get($decoded, 'listingId')) !== null ? HexConverter::prefix($listingId) : null,
        ];
    }

    /**
     * Returns the decoded listing storage data.
     */
    public function listingStorageData(string $data): array
    {
        try {
            $decoded = $this->codec->process('ListingStorageDataV1010', new ScaleBytes($data));
        } catch (\Exception) {
            $decoded = $this->codec->process('ListingStorageData', new ScaleBytes($data));
        }

        return [
            'seller' => ($seller = $this->getValue($decoded, ['seller', 'creator'])) !== null ? HexConverter::prefix($seller) : null,
            'makeAssetId' => MultiTokensTokenAssetIdParams::fromEncodable(Arr::get($decoded, 'makeAssetId')),
            'takeAssetId' => MultiTokensTokenAssetIdParams::fromEncodable(Arr::get($decoded, 'takeAssetId')),
            'amount' => gmp_strval(Arr::get($decoded, 'amount')),
            'price' => gmp_strval(Arr::get($decoded, 'price')),
            'minTakeValue' => gmp_strval($this->getValue($decoded, ['minTakeValue', 'minReceived'])),
            'feeSide' => FeeSide::from(Arr::get($decoded, 'feeSide', 'NoFee')),
            'creationBlock' => gmp_strval(Arr::get($decoded, 'creationBlock')),
            'deposit' => gmp_strval($this->getValue($decoded, ['deposit.amount', 'deposit'])),
            'salt' => Arr::get($decoded, 'salt'),
            'data' => Arr::get($decoded, 'data'),
            'state' => Arr::get($decoded, 'state'),
            // TODO: This are new fields added in v1010
            //      'depositDepositor' => Arr::get($decoded, 'deposit.depositor'),
            //      'data' => Now has FixedPrice, Auction, Offer
            //          FixedPrice = boolean
            //          Auction = { startBlock: Compact<u32>, endBlock: Compact<u32> }
            //          Offer = { expiration: Option<u32> }
            //      'state' => Now has FixedPrice, Auction, Offer
            //          FixedPrice = { amountFilled: Compact<u128> }
            //          Auction = { highBid: Option<Bid> }
            //              Bid = { bidder: AccountId, price: Compact<u128> }
            //          Offer = { counter: Option<CounterOffer> }
            //              CounterOffer = { accountId: AccountId, price: u128 }
        ];
    }
}
