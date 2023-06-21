<?php

namespace Enjin\Platform\Marketplace\Exceptions;

use Enjin\Platform\Exceptions\PlatformException;

class MarketplaceException extends PlatformException
{
    /**
     * Get the exception's category.
     */
    public function getCategory(): string
    {
        return 'Platform Marketplace';
    }
}
