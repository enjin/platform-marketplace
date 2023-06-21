<?php

namespace Enjin\Platform\Marketplace\Enums;

use Enjin\Platform\Traits\EnumExtensions;

enum ListingState: string
{
    use EnumExtensions;

    case ACTIVE = 'Active';
    case CANCELLED = 'Cancelled';
    case FINALIZED = 'Finalized';
}
