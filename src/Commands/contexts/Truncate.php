<?php

namespace Enjin\Platform\Marketplace\Commands\contexts;

class Truncate
{
    /**
     * Returns the tables to truncate.
     */
    public static function tables(): array
    {
        return [
            'marketplace_listings',
            'marketplace_bids',
            'marketplace_states',
        ];
    }
}
