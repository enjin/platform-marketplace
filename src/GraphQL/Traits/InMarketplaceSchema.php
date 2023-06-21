<?php

namespace Enjin\Platform\Marketplace\GraphQL\Traits;

use Enjin\Platform\GraphQL\Schemas\Traits\GetsMiddleware;

trait InMarketplaceSchema
{
    use GetsMiddleware;

    /**
     * The schema name.
     */
    public static function getSchemaName(): string
    {
        return 'marketplace';
    }

    /**
     * The schema network.
     */
    public static function getSchemaNetwork(): string
    {
        return '';
    }
}
