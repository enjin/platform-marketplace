<?php

namespace Enjin\Platform\Marketplace\Enums;

use Enjin\Platform\Traits\EnumExtensions;

enum ListingType: string
{
    use EnumExtensions;

    case FIXED_PRICE = 'FixedPrice';
    case AUCTION = 'Auction';
}
