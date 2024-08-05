<?php

namespace Enjin\Platform\Marketplace\GraphQL\Unions;

use Enjin\Platform\Interfaces\PlatformGraphQlUnion;
use Enjin\Platform\Marketplace\Enums\ListingType;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\UnionType;

class ListingStateUnion extends UnionType implements PlatformGraphQlUnion
{
    /**
     * Get the type's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'ListingState',
            'description' => __('enjin-platform-marketplace::enum.listing_state.description'),
        ];
    }

    /**
     * The possible types that this union can be.
     */
    public function types(): array
    {
        return [
            GraphQL::type('FixedPriceState'),
            GraphQL::type('AuctionState'),
        ];
    }

    /**
     * Resolves concrete ObjectType for given object value.
     */
    public function resolveType($objectValue, $context, ResolveInfo $info)
    {
        return match ($objectValue?->type) {
            ListingType::FIXED_PRICE->name => GraphQL::type('FixedPriceState'),
            ListingType::AUCTION->name => GraphQL::type('AuctionState'),
            default => null,
        };
    }
}
