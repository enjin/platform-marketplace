<?php

namespace Enjin\Platform\Marketplace\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;

class AuctionStateType extends Type
{
    /**
     * Get the type's attributes.
     */
    #[\Override]
    public function attributes(): array
    {
        return [
            'name' => 'AuctionState',
            'description' => __('enjin-platform-marketplace::type.auction_state.description'),
        ];
    }

    /**
     * Get the type's fields.
     */
    #[\Override]
    public function fields(): array
    {
        return [
            'type' => [
                'type' => GraphQL::type('ListingType!'),
                'description' => __('enjin-platform-marketplace::enum.listing_type.description'),
            ],
            'highestBid' => [
                'type' => GraphQL::type('MarketplaceBid'),
                'description' => __('enjin-platform-marketplace::type.auction_state.field.highestBid'),
                'is_relation' => true,
            ],
        ];
    }
}
