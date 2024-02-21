<?php

namespace Enjin\Platform\Marketplace\Models\Laravel\Traits;

use Enjin\Platform\Marketplace\GraphQL\Types\MarketplaceBidType;
use Enjin\Platform\Marketplace\GraphQL\Types\MarketplaceListingType;
use Enjin\Platform\Marketplace\GraphQL\Types\MarketplaceSaleType;
use Enjin\Platform\Marketplace\GraphQL\Types\MarketplaceStateType;
use Enjin\Platform\Models\Laravel\Traits\EagerLoadSelectFields as EagerLoadSelectFieldsBase;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Pagination\Cursor;
use Illuminate\Support\Arr;

trait EagerLoadSelectFields
{
    use EagerLoadSelectFieldsBase {
        getRelationQuery as parentGetRelationQuery;
        loadWallet as parentLoadWallet;
    }

    /**
     * Load select and relationship fields.
     */
    public static function selectFields(ResolveInfo $resolveInfo, string $query): array
    {
        $select = ['*'];
        $with = [];
        $withCount = [];
        static::$query = $query;
        $queryPlan = $resolveInfo->lookAhead()->queryPlan();

        switch($query) {
            case 'GetListings':
            case 'GetListing':
                [$select, $with, $withCount] = static::loadListings(
                    $queryPlan,
                    $query == 'GetListings' ? 'edges.fields.node.fields' : '',
                    [],
                    null,
                    true
                );

                break;
            case 'GetBids':
            case 'GetBid':
                [$select, $with, $withCount] = static::loadBids(
                    $queryPlan,
                    $query == 'GetBids' ? 'edges.fields.node.fields' : '',
                    [],
                    null,
                    true
                );

                break;
            case 'GetSales':
            case 'GetSale':
                [$select, $with, $withCount] = static::loadSales(
                    $queryPlan,
                    $query == 'GetSales' ? 'edges.fields.node.fields' : '',
                    [],
                    null,
                    true
                );

                break;
        }


        return [$select, $with, $withCount];
    }

    /**
     * Load marketplace listing's select and relationship fields.
     */
    public static function loadListings(
        array $selections,
        string $attribute,
        array $args = [],
        ?string $key = null,
        bool $isParent = false
    ): array {
        $fields = Arr::get($selections, $attribute, $selections);
        $select = array_filter([
            'id',
            'listing_chain_id',
            isset($fields['seller']) ? 'seller_wallet_id' : null,
            isset($fields['state']) || isset($fields['data']) ? 'type' : null,
            ...(isset($fields['state']) ? ['amount_filled', 'type'] : []),
            ...(isset($fields['data']) ? ['start_block', 'end_block', 'type'] : []),
            ...(isset($fields['makeAssetId']) ? ['make_collection_chain_id', 'make_token_chain_id'] : []),
            ...(isset($fields['takeAssetId']) ? ['take_collection_chain_id', 'take_token_chain_id'] : []),
            ...MarketplaceListingType::getSelectFields($fieldKeys = array_keys($fields)),
        ]);

        $with = [];
        $withCount = [];

        if (!$isParent) {
            $with = [
                $key => function ($query) use ($select, $args) {
                    $query->select(array_unique($select))
                        ->when($cursor = Cursor::fromEncoded(Arr::get($args, 'after')), fn ($q) => $q->where('id', '>', $cursor->parameter('id')))
                        ->orderBy('marketplace_listings.id');
                    // This must be done this way to load eager limit correctly.
                    if ($limit = Arr::get($args, 'first')) {
                        $query->limit($limit + 1);
                    }
                },
            ];
        }

        $relations = array_filter([
            isset($fields['state']) ? 'highestBid' : null,
            ...MarketplaceListingType::getRelationFields($fieldKeys),
        ]);
        foreach ($relations as $relation) {
            if ($isParent && in_array($relation, ['states', 'bids', 'sales'])) {
                $withCount[] = $relation;
            }

            $with = array_merge(
                $with,
                static::getRelationQuery(
                    MarketplaceListingType::class,
                    $relation,
                    $fields,
                    $key,
                    $with
                )
            );
        }

        return [$select, $with, $withCount];
    }

    /**
     * Load bids select and relationship fields.
     */
    public static function loadBids(
        array $selections,
        string $attribute,
        array $args = [],
        ?string $key = null,
        bool $isParent = false
    ): array {
        $fields = Arr::get($selections, $attribute, $selections);
        $select = array_filter([
            'id',
            'marketplace_listing_id',
            isset($fields['bidder']) ? 'wallet_id' : null,
            ...MarketplaceBidType::getSelectFields($fieldKeys = array_keys($fields)),
        ]);
        $with = [];
        $withCount = [];

        if (!$isParent) {
            $with = [
                $key => function ($query) use ($select, $args) {
                    $query->select(array_unique($select))
                        ->when($cursor = Cursor::fromEncoded(Arr::get($args, 'after')), fn ($q) => $q->where('id', '>', $cursor->parameter('id')))
                        ->orderBy('marketplace_bids.id');
                    // This must be done this way to load eager limit correctly.
                    if ($limit = Arr::get($args, 'first')) {
                        $query->limit($limit + 1);
                    }
                },
            ];
        }

        foreach (MarketplaceBidType::getRelationFields($fieldKeys) as $relation) {
            $with = array_merge(
                $with,
                static::getRelationQuery(
                    MarketplaceBidType::class,
                    $relation,
                    $fields,
                    $key,
                    $with
                )
            );
        }

        return [$select, $with, $withCount];
    }

    /**
     * Load sales select and relationship fields.
     */
    public static function loadSales(
        array $selections,
        string $attribute,
        array $args = [],
        ?string $key = null,
        bool $isParent = false
    ): array {
        $fields = Arr::get($selections, $attribute, $selections);
        $select = array_filter([
            'id',
            'listing_chain_id',
            isset($fields['bidder']) ? 'wallet_id' : null,
            ...MarketplaceSaleType::getSelectFields($fieldKeys = array_keys($fields)),
        ]);

        $with = [];
        $withCount = [];

        if (!$isParent) {
            $with = [
                $key => function ($query) use ($select, $args) {
                    $query->select(array_unique($select))
                        ->when($cursor = Cursor::fromEncoded(Arr::get($args, 'after')), fn ($q) => $q->where('id', '>', $cursor->parameter('id')))
                        ->orderBy('marketplace_sales.id');
                    // This must be done this way to load eager limit correctly.
                    if ($limit = Arr::get($args, 'first')) {
                        $query->limit($limit + 1);
                    }
                },
            ];
        }

        foreach (MarketplaceSaleType::getRelationFields($fieldKeys) as $relation) {
            $with = array_merge(
                $with,
                static::getRelationQuery(
                    MarketplaceSaleType::class,
                    $relation,
                    $fields,
                    $key,
                    $with
                )
            );
        }

        return [$select, $with, $withCount];
    }

    /**
     * Get relationship query.
     */
    public static function getRelationQuery(
        string $parentType,
        string $attribute,
        array $selections,
        ?string $parent = null,
        array $withs = []
    ): array {
        $key = $parent ? "{$parent}.{$attribute}" : $attribute;
        $alias = static::getAlias($attribute, $parentType);
        $args = Arr::get($selections, $attribute . '.args', []);
        switch($alias) {
            case 'listing':
                $relations = static::loadListings(
                    $selections,
                    $attribute . '.fields',
                    $args,
                    $key
                );
                $withs = array_merge($withs, $relations[1]);

                break;
            case 'bids':
                $relations = static::loadBids(
                    $selections,
                    $attribute . '.fields.edges.fields.node.fields',
                    $args,
                    $key
                );
                $withs = array_merge($withs, $relations[1]);

                break;
            case 'sales':
                $relations = static::loadSales(
                    $selections,
                    $attribute . '.fields.edges.fields.node.fields',
                    $args,
                    $key
                );
                $withs = array_merge($withs, $relations[1]);

                break;
            case 'states':
                $fields = Arr::get($selections, $attribute . '.fields', $selections);
                $select = collect(['id', 'marketplace_listing_id', ...MarketplaceStateType::getSelectFields(array_keys($fields))])
                    ->filter()
                    ->unique()
                    ->toArray();
                $withs = array_merge(
                    $withs,
                    [$key => fn ($query) => $query->select($select)]
                );

                break;
            case 'highestBid':
                $withs = array_merge($withs, [$key => fn ($query) => $query->select('*')]);

                break;
            case 'seller':
            case 'bidder':
                $relations = static::loadWallet(
                    $selections,
                    $attribute . '.fields',
                    $args,
                    $key
                );
                $withs = array_merge($withs, $relations[1]);

                break;
            default:
                return static::parentGetRelationQuery($parentType, $attribute, $selections, $parent, $withs);
        }

        return $withs;
    }
}
