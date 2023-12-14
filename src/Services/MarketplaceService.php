<?php

namespace Enjin\Platform\Marketplace\Services;

use Enjin\Platform\Marketplace\Models\MarketplaceListing;
use Illuminate\Database\Eloquent\Model;

class MarketplaceService
{
    /**
     * Get the collection by column and value.
     */
    public function get(string $index, string $column = 'listing_chain_id'): Model
    {
        return MarketplaceListing::where($column, '=', $index)->firstOrFail();
    }

    /**
     * Create a new collection.
     */
    public function store(array $data): Model
    {
        return MarketplaceListing::create($data);
    }

    /**
     * Insert a new collection.
     */
    public function insert(array $data): bool
    {
        return MarketplaceListing::insert($data);
    }
}
