<?php

namespace Enjin\Platform\Marketplace\GraphQL\Enums;

use Enjin\Platform\Interfaces\PlatformGraphQlEnum;
use Enjin\Platform\Marketplace\Enums\ListingState;
use Rebing\GraphQL\Support\EnumType;

class ListingStateEnum extends EnumType implements PlatformGraphQlEnum
{
    /**
     * Get the enum's attributes.
     */
    #[\Override]
    public function attributes(): array
    {
        return [
            'name' => 'ListingStateEnum',
            'values' => ListingState::caseNamesAsArray(),
            'description' => __('enjin-platform-marketplace::enum.listing_state.description'),
        ];
    }
}
