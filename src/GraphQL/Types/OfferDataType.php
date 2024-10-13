<?php

namespace Enjin\Platform\Marketplace\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;

class OfferDataType extends Type
{
    /**
     * Get the type's attributes.
     */
    #[\Override]
    public function attributes(): array
    {
        return [
            'name' => 'OfferData',
            'description' => __('enjin-platform-marketplace::type.offer_data.description'),
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
            'expiration' => [
                'type' => GraphQL::type('Int'),
                'description' => __('enjin-platform-marketplace::type.offer_data.field.expiration'),
                'alias' => 'offer_expiration',
            ],
        ];
    }
}
