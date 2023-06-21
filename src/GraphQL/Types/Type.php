<?php

namespace Enjin\Platform\Marketplace\GraphQL\Types;

use Enjin\Platform\Interfaces\PlatformGraphQlType;
use Enjin\Platform\Marketplace\GraphQL\Traits\InMarketplaceSchema;
use Rebing\GraphQL\Support\Type as GraphQlType;

abstract class Type extends GraphQlType implements PlatformGraphQlType
{
    use InMarketplaceSchema;
}
