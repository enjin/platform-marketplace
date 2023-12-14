<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Events\Implementations\Traits;

use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\Marketplace\Models\MarketplaceListing;
use Enjin\Platform\Services\Processor\Substrate\Events\Implementations\Traits\QueryDataOrFail as QueryDataOrFailBase;
use Illuminate\Database\Eloquent\Model;

trait QueryDataOrFail
{
    use QueryDataOrFailBase;

    /**
     * Returns the listing with the specified listing ID.
     */
    protected function getListing(string $listingId): Model
    {
        if (!$listing = MarketplaceListing::where(['listing_chain_id' => $listingId])->first()) {
            throw new PlatformException(__('enjin-platform::traits.query_data_or_fail.unable_to_find_listing', ['class' => __CLASS__, 'listingId' => $listingId]));
        }

        return $listing;
    }
}
