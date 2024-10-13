<?php

namespace Enjin\Platform\Marketplace\GraphQL\Types;

use Rebing\GraphQL\Support\Facades\GraphQL;

class AssetType extends Type
{
    /**
     * Get the type's attributes.
     */
    #[\Override]
    public function attributes(): array
    {
        return [
            'name' => 'Asset',
            'description' => __('enjin-platform-marketplace::type.asset.description'),
        ];
    }

    /**
     * Get the type's fields.
     */
    #[\Override]
    public function fields(): array
    {
        return [
            'collectionId' => [
                'type' => GraphQL::type('BigInt!'),
                'description' => __('enjin-platform-marketplace::type.asset.field.collectionId'),
            ],
            'tokenId' => [
                'type' => GraphQL::type('BigInt!'),
                'description' => __('enjin-platform-marketplace::type.asset.field.tokenId'),
            ],
        ];
    }
}
