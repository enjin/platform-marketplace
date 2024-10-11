<?php

namespace Enjin\Platform\Marketplace\GraphQL\Types\Input;

use Enjin\Platform\Marketplace\GraphQL\Types\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;

class OfferParamsInputType extends InputType
{
    /**
     * Get the type's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'OfferParamsInput',
            'description' => __('enjin-platform-marketplace::type.listing_data.description'),
        ];
    }

    /**
     * Get the type's fields.
     */
    public function fields(): array
    {
        return [
            'expiration' => [
                'type' => GraphQL::type('Int'),
                'description' => __('enjin-platform-marketplace::type.auction_data.field.startBlock'),
            ],
        ];
    }
}
