<?php

namespace Enjin\Platform\Marketplace\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;

class AuctionDataType extends Type
{
    /**
     * Get the type's attributes.
     */
    #[\Override]
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
    #[\Override]
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
                'alias' => 'auction_start_block',
            ],
            'endBlock' => [
                'type' => GraphQL::type('Int!'),
                'description' => __('enjin-platform-marketplace::type.auction_data.field.endBlock'),
                'alias' => 'auction_end_block',
            ],
        ];
    }
}
