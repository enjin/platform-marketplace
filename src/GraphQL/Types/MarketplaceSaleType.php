<?php

namespace Enjin\Platform\Marketplace\GraphQL\Types;

use Enjin\Platform\Traits\HasSelectFields;
use Rebing\GraphQL\Support\Facades\GraphQL;

class MarketplaceSaleType extends Type
{
    use HasSelectFields;

    /**
     * Get the type's attributes.
     */
    #[\Override]
    public function attributes(): array
    {
        return [
            'name' => 'MarketplaceSale',
            'description' => __('enjin-platform-marketplace::type.marketplace_sale.description'),
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
            'price' => [
                'type' => GraphQL::type('BigInt!'),
                'description' => __('enjin-platform-marketplace::type.marketplace_listing.field.price'),
            ],
            'amount' => [
                'type' => GraphQL::type('BigInt!'),
                'description' => __('enjin-platform-marketplace::type.marketplace_listing.field.amount'),
            ],
            'bidder' => [
                'type' => GraphQL::type('Wallet!'),
                'description' => __('enjin-platform-marketplace::type.marketplace_bid.field.bidder'),
                'is_relation' => true,
            ],
            'listing' => [
                'type' => GraphQL::type('MarketplaceListing'),
                'description' => __('enjin-platform-marketplace::type.marketplace_listing.description'),
                'is_relation' => true,
            ],
        ];
    }
}
