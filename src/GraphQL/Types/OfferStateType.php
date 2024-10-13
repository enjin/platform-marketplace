<?php

namespace Enjin\Platform\Marketplace\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;

class OfferStateType extends Type
{
    /**
     * Get the type's attributes.
     */
    #[\Override]
    public function attributes(): array
    {
        return [
            'name' => 'OfferState',
            'description' => __('enjin-platform-marketplace::type.offer_state.description'),
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
            'counterOfferCount' => [
                'type' => GraphQL::type('Int!'),
                'description' => __('enjin-platform-marketplace::type.offer_state.field.counterOfferCount'),
                'alias' => 'counter_offer_count',
            ],
        ];
    }
}
