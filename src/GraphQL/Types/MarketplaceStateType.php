<?php

namespace Enjin\Platform\Marketplace\GraphQL\Types;

use Enjin\Platform\Traits\HasSelectFields;
use Rebing\GraphQL\Support\Facades\GraphQL;

class MarketplaceStateType extends Type
{
    use HasSelectFields;

    /**
     * Get the type's attributes.
     */
    #[\Override]
    public function attributes(): array
    {
        return [
            'name' => 'MarketplaceState',
            'description' => __('enjin-platform-marketplace::type.marketplace_state.description'),
        ];
    }

    /**
     * Get the type's fields.
     */
    #[\Override]
    public function fields(): array
    {
        return [
            'id' => [
                'type' => GraphQL::type('BigInt!'),
                'description' => __('enjin-platform-marketplace::type.marketplace_bid.field.id'),
            ],
            'state' => [
                'type' => GraphQL::type('ListingStateEnum!'),
                'description' => __('enjin-platform-marketplace::type.listing_state.description'),
            ],
            'height' => [
                'type' => GraphQL::type('Int!'),
                'description' => __('enjin-platform-marketplace::type.marketplace_state.field.height'),
                'alias' => 'height',
            ],
            'listing' => [
                'type' => GraphQL::type('MarketplaceListing!'),
                'description' => __('enjin-platform-marketplace::type.marketplace_listing.description'),
                'is_relation' => true,
            ],
        ];
    }
}
