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
        $decoded = $this->codec->process('ListingStorageData', new ScaleBytes($data));

        return [
            'seller' => ($seller = Arr::get($decoded, 'seller')) !== null ? HexConverter::prefix($seller) : null,
            'makeAssetId' => MultiTokensTokenAssetIdParams::fromEncodable(Arr::get($decoded, 'makeAssetId')),
            'takeAssetId' => MultiTokensTokenAssetIdParams::fromEncodable(Arr::get($decoded, 'takeAssetId')),
            'amount' => gmp_strval(Arr::get($decoded, 'amount')),
            'price' => gmp_strval(Arr::get($decoded, 'price')),
            'minTakeValue' => gmp_strval(Arr::get($decoded, 'minTakeValue')),
            'feeSide' => FeeSide::from(Arr::get($decoded, 'feeSide', 'NoFee')),
            'creationBlock' => gmp_strval(Arr::get($decoded, 'creationBlock')),
            'deposit' => gmp_strval(Arr::get($decoded, 'deposit')),
            'salt' => Arr::get($decoded, 'salt'),
            'data' => Arr::get($decoded, 'data'), // TODO: Check
            'state' => Arr::get($decoded, 'state'), // TODO; Check
        ];
    }
}
