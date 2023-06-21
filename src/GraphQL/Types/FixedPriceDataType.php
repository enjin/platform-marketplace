<?php

namespace Enjin\Platform\Marketplace\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;

class FixedPriceDataType extends Type
{
    /**
     * Get the type's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'FixedPriceData',
            'description' => __('enjin-platform-marketplace::type.fixed_price_data.description'),
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
        ];
    }
}
