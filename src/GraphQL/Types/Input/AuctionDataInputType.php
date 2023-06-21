<?php

namespace Enjin\Platform\Marketplace\GraphQL\Types\Input;

use Rebing\GraphQL\Support\Facades\GraphQL;

class AuctionDataInputType extends InputType
{
    /**
     * Get the input type's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'AuctionDataInputType',
            'description' => __('enjin-platform-marketplace::input_type.auction_data.description'),
        ];
    }

    /**
     * Get the input type's fields.
     */
    public function fields(): array
    {
        return [
            'startBlock' => [
                'type' => GraphQL::type('Int!'),
                'description' => __('enjin-platform-marketplace::type.auction_data.field.startBlock'),
            ],
            'endBlock' => [
                'type' => GraphQL::type('Int!'),
                'description' => __('enjin-platform-marketplace::type.auction_data.field.endBlock'),
            ],
        ];
    }
}
