<?php

namespace Enjin\Platform\Marketplace\GraphQL\Enums;

use Enjin\Platform\Interfaces\PlatformGraphQlEnum;
use Enjin\Platform\Marketplace\Enums\FeeSide;
use Rebing\GraphQL\Support\EnumType;

class FeeSideEnum extends EnumType implements PlatformGraphQlEnum
{
    /**
     * Get the enum's attributes.
     */
    public function attributes(): array
    {
        return [
            'name' => 'FeeSide',
            'values' => FeeSide::caseNamesAsArray(),
            'description' => __('enjin-platform-marketplace::enum.fee_side.description'),
        ];
    }
}
