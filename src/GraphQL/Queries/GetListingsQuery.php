<?php

namespace Enjin\Platform\Marketplace\GraphQL\Queries;

use Closure;
use Enjin\Platform\GraphQL\Middleware\ResolvePage;
use Enjin\Platform\GraphQL\Types\Pagination\ConnectionInput;
use Enjin\Platform\Marketplace\Models\MarketplaceListing;
use Enjin\Platform\Rules\MaxBigInt;
use Enjin\Platform\Rules\MinBigInt;
use Enjin\Platform\Rules\ValidSubstrateAddress;
use Enjin\Platform\Support\Hex;
use Enjin\Platform\Support\SS58Address;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Arr;
use Rebing\GraphQL\Support\Facades\GraphQL;

class GetListingsQuery extends Query
{
    protected $middleware = [
        ResolvePage::class,
    ];

    /**
     * Get the mutation's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'GetListings',
            'description' => __('enjin-platform-marketplace::query.get_listings.description'),
        ];
    }

    /**
     * Get the mutation's return type.
     */
    public function type(): Type
    {
        return GraphQL::paginate('MarketplaceListing', 'MarketplaceListingConnection');
    }

    /**
     * Get the mutation's arguments definition.
     */
    public function args(): array
    {
        return ConnectionInput::args([
            'ids' => [
                'type' => GraphQL::type('[BigInt!]'),
                'description' => __('enjin-platform-marketplace::type.marketplace_bid.field.id'),
            ],
            'listingIds' => [
                'type' => GraphQL::type('[String!]'),
                'description' => __('enjin-platform-marketplace::type.marketplace_listing.field.listingId'),
            ],
            'account' => [
                'type' => GraphQL::type('String'),
                'description' => __('enjin-platform-marketplace::query.get_listings.args.account'),
            ],
            'makeAssetId' => [
                'type' => GraphQL::type('AssetInputType'),
                'description' => __('enjin-platform-marketplace::type.marketplace_listing.field.makeAssetId'),
            ],
            'takeAssetId' => [
                'type' => GraphQL::type('AssetInputType'),
                'description' => __('enjin-platform-marketplace::type.marketplace_listing.field.takeAssetId'),
            ],
        ]);
    }

    /**
     * Resolve the mutation's request.
     */
    public function resolve(
        $root,
        array $args,
        $context,
        ResolveInfo $resolveInfo,
        Closure $getSelectFields
    ) {
        return MarketplaceListing::loadSelectFields($resolveInfo, $this->name)
            ->when(
                $ids = Arr::get($args, 'ids'),
                fn ($query) => $query->whereIn('id', $ids)
            )->when(
                $listingIds = Arr::get($args, 'listingIds'),
                fn ($query) => $query->whereIn('listing_id', $listingIds)
            )->when(
                $account = Arr::get($args, 'account'),
                fn ($query) => $query->whereHas(
                    'seller',
                    fn ($query) => $query->where('public_key', SS58Address::getPublicKey($account))
                )
            )->when(
                $makeAsset = Arr::get($args, 'makeAssetId'),
                fn ($query) => $query->where([
                    'make_collection_chain_id' =>  Arr::get($makeAsset, 'collectionId'),
                    'make_token_chain_id' => Arr::get($makeAsset, 'tokenId'),
                ])
            )->when(
                $takeAsset = Arr::get($args, 'takeAssetId'),
                fn ($query) => $query->where([
                    'take_collection_chain_id' =>  Arr::get($takeAsset, 'collectionId'),
                    'take_token_chain_id' => Arr::get($takeAsset, 'tokenId'),
                ])
            )->cursorPaginateWithTotalDesc('marketplace_listings.id', $args['first']);
    }

    /**
     * Get the mutation's request validation rules.
     */
    protected function rules(array $args = []): array
    {
        return [
            'ids' => [
                'bail',
                'array',
                'prohibits:listingIds',
                'max:1000',
            ],
            'ids.*' => [
                'bail',
                new MinBigInt(),
                new MaxBigInt(),
            ],
            'listingIds' => [
                'bail',
                'array',
                'prohibits:ids',
                'max:1000',
            ],
            'listingIds.*' => ['max:255'],
            'account' => [
                'bail',
                'max:255',
                new ValidSubstrateAddress(),
            ],
            'makeAssetId.collectionId' => [
                'bail',
                'required_with:makeAssetId.tokenId',
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT128),
            ],
            'makeAssetId.tokenId' => [
                'bail',
                'required_with:makeAssetId.collectionId',
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT128),
            ],
            'takeAssetId.collectionId' => [
                'bail',
                'required_with:takeAssetId.tokenId',
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT128),
            ],
            'takeAssetId.tokenId' => [
                'bail',
                'required_with:takeAssetId.collectionId',
                new MinBigInt(),
                new MaxBigInt(Hex::MAX_UINT128),
            ],
        ];
    }
}
