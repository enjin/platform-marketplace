<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Codec;

use Enjin\Platform\Services\Processor\Substrate\Codec\Encoder as BaseEncoder;

class Encoder extends BaseEncoder
{
    protected static array $callIndexKeys = [
        'CreateListing' => 'Marketplace.create_listing',
        'CreateListingV1013' => 'Marketplace.create_listing',
        'CancelListing' => 'Marketplace.cancel_listing',
        'FillListing' => 'Marketplace.fill_listing',
        'FillListingV1013' => 'Marketplace.fill_listing',
        'FinalizeAuction' => 'Marketplace.finalize_auction',
        'PlaceBid' => 'Marketplace.place_bid',
        'SetProtocolFee' => 'Marketplace.set_protocol_fee',
    ];
}
