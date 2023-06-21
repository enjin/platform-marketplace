<?php

namespace Enjin\Platform\Marketplace\GraphQL\Queries;

use Enjin\Platform\Interfaces\PlatformGraphQlQuery;
use Enjin\Platform\Marketplace\GraphQL\Traits\InMarketplaceSchema;
use Rebing\GraphQL\Support\Query as GraphQlQuery;

abstract class Query extends GraphQlQuery implements PlatformGraphQlQuery
{
    use InMarketplaceSchema;
}
