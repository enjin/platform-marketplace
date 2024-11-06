<?php

return [
    'create_listing.description' => 'Places a sell order.',
    'create_listing.args.account' => 'The seller account.',
    'create_listing.args.makeAssetId' => 'Ids for the asset being sold.',
    'create_listing.args.takeAssetId' => 'Ids for the asset requested.',
    'create_listing.args.listingData' => 'The listing data parameters.',
    'cancel_listing.description' => 'Cancels the listing.',
    'fill_listing.description' => 'Fills a fixed price listing.',
    'finalize_auction.description' => 'This will end the auction and transfer funds. It fails if the auction is not over.',
    'place_bid.description' => 'Places a bid on a listing.The listing must be an auction, and it must be currently active.',
    'set_protocol_fee.description' => 'Change the protocol fee.',
];
