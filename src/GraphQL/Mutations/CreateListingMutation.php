<?php

namespace Enjin\Platform\Marketplace\GraphQL\Mutations;

use Closure;
use Enjin\Platform\GraphQL\Schemas\Primary\Traits\HasTokenIdFieldRules;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasIdempotencyField;
use Enjin\Platform\Interfaces\PlatformBlockchainTransaction;
use Enjin\Platform\Marketplace\Rules\EnoughTokenSupply;
use Enjin\Platform\Marketplace\Rules\FutureBlock;
use Enjin\Platform\Marketplace\Rules\TokenExistsInCollection;
use Enjin\Platform\Marketplace\Services\TransactionService;
use Enjin\Platform\Models\Transaction;
use Enjin\Platform\Rules\MaxBigInt;
use Enjin\Platform\Rules\MinBigInt;
use Enjin\Platform\Rules\ValidSubstrateAddress;
use Enjin\Platform\Support\Hex;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\Facades\GraphQL;

class CreateListingMutation extends Mutation implements PlatformBlockchainTransaction
{
    use HasIdempotencyField;
    use HasTokenIdFieldRules;

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
            'account' => [
                'type' => GraphQL::type('String!'),
                'description' => __('enjin-platform-marketplace::mutation.create_listing.args.account'),
            ],
            'makeAssetId' => [
                'type' => GraphQL::type('MultiTokenIdInput!'),
                'description' => __('enjin-platform-marketplace::create_listing.args.makeAsset'),
            ],
            'takeAssetId' => [
                'type' => GraphQL::type('MultiTokenIdInput!'),
                'description' => __('enjin-platform-marketplace::create_listing.args.takeAsset'),
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
            'auctionData' => [
                'type' => GraphQL::type('AuctionDataInputType'),
                'description' => __('enjin-platform-marketplace::input_type.auction_data.description'),
            ],
            ...$this->getIdempotencyField(),
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
        TransactionService $transaction
    ) {
        return Transaction::lazyLoadSelectFields(
            DB::transaction(fn () => $transaction->createListing($args)),
            $resolveInfo
        );
    }

    /**
     * Get the mutation's request validation rules.
     */
    protected function rules(array $args = []): array
    {
        return [
            'account' => [
                'bail',
                'filled',
                'max:255',
                new ValidSubstrateAddress(),
            ],
            'makeAssetId.collectionId' => [
                'bail',
                'required_with:makeAssetId.tokenId',
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT128),
                Rule::exists('collections', 'collection_chain_id'),
            ],
            ...$this->getTokenFieldRules('makeAssetId'),
            'makeAssetId' => new TokenExistsInCollection(Arr::get($args, 'makeAssetId.collectionId')),
            'takeAssetId.collectionId' => [
                'bail',
                'required_with:takeAsset.tokenId',
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT128),
                Rule::exists('collections', 'collection_chain_id'),
            ],
            ...$this->getTokenFieldRules('takeAssetId'),
            'takeAssetId' => new TokenExistsInCollection(Arr::get($args, 'takeAssetId.collectionId')),
            'amount' => [
                'bail',
                new MinBigInt(1),
                new MaxBigInt(),
                new EnoughTokenSupply(),
            ],
            'price' => [
                'bail',
                new MinBigInt(),
                new MaxBigInt(),
            ],
            'salt' => ['bail', 'filled', 'max:255'],
            'auctionData.startBlock' => [
                'bail',
                'required_with:auctionData.endBlock',
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT32),
                new FutureBlock(),
                'lte:auctionData.endBlock',
            ],
            'auctionData.endBlock' => [
                'bail',
                'required_with:auctionData.startBlock',
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT32),
                'gt:auctionData.startBlock',
            ],
        ];
    }
}
