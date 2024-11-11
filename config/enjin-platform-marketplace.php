<?php

return [
    /**
     * The block offset to use for validation when creating a listing.
     */
    'block_offset' => env('LISTING_BLOCK_OFFSET', 10),

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Here you may set the dedicated queue for this package
    |
    */
    'queue' => env('PLATFORM_MARKETPLACE_QUEUE', 'default'),
];
