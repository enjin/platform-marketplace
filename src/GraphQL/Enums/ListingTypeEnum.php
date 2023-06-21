<?php

namespace Enjin\Platform\Marketplace\GraphQL\Enums;

use Enjin\Platform\Interfaces\PlatformGraphQlEnum;
use Enjin\Platform\Marketplace\Enums\ListingType;
use Rebing\GraphQL\Support\EnumType;

class ListingTypeEnum extends EnumType implements PlatformGraphQlEnum
{
    /**
     * Get the enum's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'ListingType',
            'values' => ListingType::caseNamesAsArray(),
            'description' => __('enjin-platform-marketplace::enum.listing_type.description'),
        ];
    }
}
