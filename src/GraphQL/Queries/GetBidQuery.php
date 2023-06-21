<?php

namespace Enjin\Platform\Marketplace\GraphQL\Queries;

use Closure;
use Enjin\Platform\Marketplace\Models\MarketplaceBid;
use Enjin\Platform\Rules\MaxBigInt;
use Enjin\Platform\Rules\MinBigInt;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\Facades\GraphQL;

class GetBidQuery extends Query
{
    /**
     * Get the mutation's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'GetBid',
            'description' => __('enjin-platform-marketplace::query.get_bid.description'),
        ];
    }

    /**
     * Get the mutation's return type.
     */
    public function type(): Type
    {
        return GraphQL::type('MarketplaceBid!');
    }

    /**
     * Get the mutation's arguments definition.
     */
    public function args(): array
    {
        return [
            'id' => [
                'type' => GraphQL::type('BigInt!'),
                'description' => __('enjin-platform-marketplace::type.marketplace_bid.field.id'),
            ],
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
        Closure $getSelectFields
    ) {
        return MarketplaceBid::loadSelectFields($resolveInfo, $this->name)->find($args['id']);
    }

    /**
     * Get the mutation's request validation rules.
     */
    protected function rules(array $args = []): array
    {
        return [
            'id' => [
                'bail',
                new MinBigInt(),
                new MaxBigInt(),
                Rule::exists('marketplace_bids', 'id'),
            ],
        ];
    }
}
