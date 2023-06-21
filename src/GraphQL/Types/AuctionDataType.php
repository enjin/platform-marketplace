<?php

namespace Enjin\Platform\Marketplace\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;

class AuctionDataType extends Type
{
    /**
     * Get the type's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'AuctionData',
            'description' => __('enjin-platform-marketplace::type.auction_data.description'),
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
            'startBlock' => [
                'type' => GraphQL::type('Int!'),
                'description' => __('enjin-platform-marketplace::type.auction_data.field.startBlock'),
                'alias' => 'start_block',
            ],
            'endBlock' => [
                'type' => GraphQL::type('Int!'),
                'description' => __('enjin-platform-marketplace::type.auction_data.field.endBlock'),
                'alias' => 'end_block',
            ],
        ];
    }
}
