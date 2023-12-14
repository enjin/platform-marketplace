<?php

namespace Enjin\Platform\Marketplace\GraphQL\Queries;

use Closure;
use Enjin\Platform\GraphQL\Middleware\ResolvePage;
use Enjin\Platform\GraphQL\Types\Pagination\ConnectionInput;
use Enjin\Platform\Marketplace\Models\MarketplaceSale;
use Enjin\Platform\Rules\MaxBigInt;
use Enjin\Platform\Rules\MinBigInt;
use Enjin\Platform\Rules\ValidSubstrateAddress;
use Enjin\Platform\Support\SS58Address;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Arr;
use Rebing\GraphQL\Support\Facades\GraphQL;

class GetSalesQuery extends Query
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
            'name' => 'GetSales',
            'description' => __('enjin-platform-marketplace::query.get_sales.description'),
        ];
    }

    /**
     * Get the mutation's return type.
     */
    public function type(): Type
    {
        return GraphQL::paginate('MarketplaceSale', 'MarketplaceSaleConnection');
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
            'accounts' => [
                'type' => GraphQL::type('[String]'),
                'description' => __('enjin-platform-marketplace::query.get_listings.args.account'),
            ],
            'listingIds' => [
                'type' => GraphQL::type('[String]'),
                'description' => __('enjin-platform-marketplace::type.marketplace_listing.field.listingId'),
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
        return MarketplaceSale::loadSelectFields($resolveInfo, $this->name)
            ->when(
                $ids = Arr::get($args, 'ids'),
                fn ($query) => $query->whereIn('id', $ids)
            )->when(
                $accounts = Arr::get($args, 'accounts'),
                fn ($query) => $query->whereHas(
                    'bidder',
                    fn ($query) => $query->whereIn(
                        'public_key',
                        collect($accounts)->map(fn ($account) => SS58Address::getPublicKey($account))->toArray()
                    )
                )
            )->when(
                $listingIds = Arr::get($args, 'listingIds'),
                fn ($query) => $query->whereHas(
                    'listing',
                    fn ($query) => $query->whereIn('listing_chain_id', $listingIds)
                )
            )->cursorPaginateWithTotalDesc('marketplace_sales.id', $args['first']);
    }

    /**
     * Get the mutation's request validation rules.
     */
    protected function rules(array $args = []): array
    {
        return [
            'ids' => ['bail', 'nullable', 'array', 'max:1000'],
            'ids.*' => [
                'bail',
                new MinBigInt(),
                new MaxBigInt(),
            ],
            'listingIds' => ['bail', 'nullable', 'array', 'max:1000'],
            'listingIds.*' => ['max:255'],
            'accounts' => ['bail', 'nullable', 'array', 'max:1000'],
            'accounts.*' => [
                'bail',
                'max:255',
                Arr::get($args, 'accounts') ? new ValidSubstrateAddress() : '',
            ],
        ];
    }
}
