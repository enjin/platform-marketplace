<?php

namespace Enjin\Platform\Marketplace\GraphQL\Mutations;

use Closure;
use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Traits\StoresTransactions;
use Enjin\Platform\GraphQL\Schemas\Primary\Traits\HasSkippableRules;
use Enjin\Platform\GraphQL\Schemas\Primary\Traits\HasTokenIdFieldRules;
use Enjin\Platform\GraphQL\Schemas\Primary\Traits\HasTransactionDeposit;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasIdempotencyField;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasSigningAccountField;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasSimulateField;
use Enjin\Platform\Interfaces\PlatformBlockchainTransaction;
use Enjin\Platform\Marketplace\Models\Substrate\AuctionDataParams;
use Enjin\Platform\Marketplace\Models\Substrate\MultiTokensTokenAssetIdParams;
use Enjin\Platform\Marketplace\Rules\EnoughTokenSupply;
use Enjin\Platform\Marketplace\Rules\FutureBlock;
use Enjin\Platform\Marketplace\Rules\TokenExistsInCollection;
use Enjin\Platform\Models\Collection;
use Enjin\Platform\Models\Transaction;
use Enjin\Platform\Rules\MaxBigInt;
use Enjin\Platform\Rules\MinBigInt;
use Enjin\Platform\Support\Hex;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Rebing\GraphQL\Support\Facades\GraphQL;

class CreateListingMutation extends Mutation implements PlatformBlockchainTransaction
{
    use HasIdempotencyField;
    use HasSigningAccountField;
    use HasSimulateField;
    use HasSkippableRules;
    use HasTokenIdFieldRules;
    use HasTransactionDeposit;
    use StoresTransactions;

    /**
     * Get the mutation's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'CreateListing',
            'description' => __('enjin-platform-marketplace::mutation.create_listing.description'),
        ];
    }

    /**
     * Get the mutation's return type.
     */
    public function type(): Type
    {
        return GraphQL::type('Transaction!');
    }

    /**
     * Get the mutation's arguments definition.
     */
    public function args(): array
    {
        return [
            'makeAssetId' => [
                'type' => GraphQL::type('MultiTokenIdInput!'),
                'description' => __('enjin-platform-marketplace::mutation.create_listing.args.makeAssetId'),
            ],
            'takeAssetId' => [
                'type' => GraphQL::type('MultiTokenIdInput!'),
                'description' => __('enjin-platform-marketplace::mutation.create_listing.args.takeAssetId'),
            ],
            'amount' => [
                'type' => GraphQL::type('BigInt!'),
                'description' => __('enjin-platform-marketplace::type.marketplace_listing.field.amount'),
            ],
            'price' => [
                'type' => GraphQL::type('BigInt!'),
                'description' => __('enjin-platform-marketplace::type.marketplace_listing.field.price'),
            ],
            'salt' => [
                'type' => GraphQL::type('String'),
                'description' => __('enjin-platform-marketplace::type.marketplace_listing.field.salt'),
            ],
            // TODO: We should remove `auctionData` and replace it with `listingData`
            //  listingData = FixedPrice, Auction, Offer
            //      FixedPrice => null,
            //      Auction => { startBlock, endBlock },
            //      Offer => { expiration }
            'auctionData' => [
                'type' => GraphQL::type('AuctionDataInputType'),
                'description' => __('enjin-platform-marketplace::input_type.auction_data.description'),
            ],
            ...$this->getSigningAccountField(),
            ...$this->getIdempotencyField(),
            ...$this->getSimulateField(),
            ...$this->getSkipValidationField(),
        ];
    }

    /**
     * Resolve the mutation's request.
     */
    public function resolve(
        $root,
        array $args,
        $context,
        ResolveInfo $resolveInfo,
        Closure $getSelectFields,
    ) {
        $method = isRunningLatest() ? $this->getMutationName() . 'V1010' : $this->getMutationName();
        $encodedData = TransactionSerializer::encode($method, static::getEncodableParams(
            makeAssetId: new MultiTokensTokenAssetIdParams(
                Arr::get($args, 'makeAssetId.collectionId'),
                $this->encodeTokenId(Arr::get($args, 'makeAssetId'))
            ),
            takeAssetId: new MultiTokensTokenAssetIdParams(
                Arr::get($args, 'takeAssetId.collectionId'),
                $this->encodeTokenId(Arr::get($args, 'takeAssetId'))
            ),
            amount: Arr::get($args, 'amount'),
            price: Arr::get($args, 'price'),
            salt: Arr::get($args, 'salt', Str::random(10)),
            auctionData: ($data = Arr::get($args, 'auctionData'))
                ? new AuctionDataParams(Arr::get($data, 'startBlock'), Arr::get($data, 'endBlock'))
                : null
        ));

        return Transaction::lazyLoadSelectFields(
            DB::transaction(fn () => $this->storeTransaction($args, $encodedData)),
            $resolveInfo
        );
    }

    public static function getEncodableParams(...$params): array
    {
        $makeAsset = Arr::get($params, 'makeAssetId', new MultiTokensTokenAssetIdParams('0', '0'));
        $takeAsset = Arr::get($params, 'takeAssetId', new MultiTokensTokenAssetIdParams('0', '0'));
        $amount = Arr::get($params, 'amount', 0);
        $price = Arr::get($params, 'price', 0);
        $salt = Arr::get($params, 'salt', Str::random(10));
        $auctionData = Arr::get($params, 'auctionData');

        $extra = isRunningLatest() ? [
            'listingData' => $auctionData ? [
                'Auction' => $auctionData->toEncodable(),
            ] : [
                'FixedPrice' => null,
            ],
        ] : [
            'auctionData' => $auctionData?->toEncodable(),
        ];

        return [
            'makeAssetId' => $makeAsset->toEncodable(),
            'takeAssetId' => $takeAsset->toEncodable(),
            'amount' => gmp_init($amount),
            'price' => gmp_init($price),
            'salt' => HexConverter::stringToHexPrefixed($salt),
            ...$extra,
            'depositor' => null,
        ];
    }

    protected function makeOrTakeRuleExist(?string $collectionId = null, ?bool $isMake = true): array
    {
        $makeOrTake = $isMake ? 'makeAssetId' : 'takeAssetId';

        return $collectionId === '0' ? [] : [
            $makeOrTake . '.collectionId' => [
                'bail',
                'required_with:' . $makeOrTake . '.tokenId',
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT128),
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (!Collection::where('collection_chain_id', $value)->exists()) {
                        $fail('validation.exists')->translate();
                    }
                },
            ],
        ];
    }

    protected function makeOrTakeRule(?string $collectionId = null, ?bool $isMake = true): array
    {
        $makeOrTake = $isMake ? 'makeAssetId' : 'takeAssetId';

        return $collectionId === '0' ? [] : [
            $makeOrTake . '.collectionId' => [
                'bail',
                'required_with:' . $makeOrTake . '.tokenId',
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT128),
            ],
        ];
    }

    /**
     * Get the common rules.
     */
    protected function rulesCommon(array $args): array
    {
        return [
            'price' => [
                'bail',
                new MinBigInt(),
                new MaxBigInt(),
            ],
            'salt' => ['bail', 'filled', 'max:255'],
            'auctionData.endBlock' => [
                'bail',
                'required_with:auctionData.startBlock',
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT32),
                'gt:auctionData.startBlock',
            ],
        ];
    }

    /**
     * Get the mutation's validation rules.
     */
    protected function rulesWithValidation(array $args): array
    {
        $makeRule = $this->makeOrTakeRuleExist($makeCollection = Arr::get($args, 'makeAssetId.collectionId'));
        $takeRule = $this->makeOrTakeRuleExist($takeCollection = Arr::get($args, 'takeAssetId.collectionId'), false);

        return [
            'makeAssetId' => new TokenExistsInCollection($makeCollection),
            ...$makeRule,
            ...$this->getTokenFieldRules('makeAssetId'),
            'takeAssetId' => new TokenExistsInCollection($takeCollection),
            ...$takeRule,
            ...$this->getTokenFieldRules('takeAssetId'),
            'amount' => [
                'bail',
                new MinBigInt(1),
                new MaxBigInt(),
                new EnoughTokenSupply(),
            ],
            'auctionData.startBlock' => [
                'bail',
                'required_with:auctionData.endBlock',
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT32),
                new FutureBlock(),
                'lte:auctionData.endBlock',
            ],
        ];
    }

    /**
     * Get the mutation's validation rules without DB rules.
     */
    protected function rulesWithoutValidation(array $args): array
    {
        $makeRule = $this->makeOrTakeRule(Arr::get($args, 'makeAssetId.collectionId'));
        $takeRule = $this->makeOrTakeRule(Arr::get($args, 'takeAssetId.collectionId'), false);

        return [
            ...$makeRule,
            ...$this->getTokenFieldRules('makeAssetId'),
            ...$takeRule,
            ...$this->getTokenFieldRules('takeAssetId'),
            'amount' => [
                'bail',
                new MinBigInt(1),
                new MaxBigInt(),
            ],
            'auctionData.startBlock' => [
                'bail',
                'required_with:auctionData.endBlock',
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT32),
                'lte:auctionData.endBlock',
            ],
        ];
    }
}
