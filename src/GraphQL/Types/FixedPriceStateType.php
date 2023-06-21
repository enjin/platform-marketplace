<?php

namespace Enjin\Platform\Marketplace\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;

class FixedPriceStateType extends Type
{
    /**
     * Get the type's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'FixedPriceState',
            'description' => __('enjin-platform-marketplace::type.fixed_price_state.description'),
        ];
    }

    /**
     * Get the type's fields.
     */
    public function fields(): array
    {
        return [
            'type' => [
                'type' => GraphQL::type('ListingType!'),
                'description' => __('enjin-platform-marketplace::enum.listing_type.description'),
            ],
            'amountFilled' => [
                'type' => GraphQL::type('BigInt'),
                'description' => __('enjin-platform-marketplace::type.fixed_price_state.field.amountFilled'),
                'alias' => 'amount_filled',
            ],
        ];
    }
}
