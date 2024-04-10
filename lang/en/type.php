<?php

return [
    'marketplace_listing.description' => 'A marketplace listing detail.',
    'marketplace_listing.field.seller' => 'The account making the sale.',
    'marketplace_listing.field.listingId' => 'The listing ID.',
    'marketplace_listing.field.makeAssetId' => 'The collection and token ID of the asset being sold.',
    'marketplace_listing.field.takeAssetId' => 'The collection and token ID of the asset being requested.',
    'marketplace_listing.field.amount' => 'The number of units being sold.',
    'marketplace_listing.field.price' => 'The requested price for each unit. If it’s an auction, this is the minimum bid.',
    'marketplace_listing.field.minTakeValue' => 'The minimum value of the take asset received for the sale to be a success.',
    'marketplace_listing.field.feeSide' => 'The side of the listing that is considered money and is used to pay fees.',
    'marketplace_listing.field.creationBlock' => 'The block number the listing was created.',
    'marketplace_listing.field.deposit' => 'The deposit that was reserved for this listing.',
    'marketplace_listing.field.salt' => 'Can be used to differentiate listings.',
    'marketplace_listing.field.type' => 'A type of listing.',
    'marketplace_bid.field.id' => 'The internal ID.',
    'asset.description' => 'A token asset.',
    'asset.field.collectionId' => 'The asset collection ID.',
    'asset.field.tokenId' => 'The asset token ID.',
    'listing_state.description' => 'Mutable state of a listing.',
    'fixed_price_state.description' => 'State of a fixed price listing.',
    'fixed_price_state.field.amountFilled' => 'The amount of the listing that has been filled.',
    'auction_state.description' => 'State of an auction listing.',
    'auction_state.field.highestBid' => 'The highest bid.',
    'auction_data.description' => 'Immutable data specifically for an auction.',
    'auction_data.field.startBlock' => 'The block number the auction starts at.',
    'auction_data.field.endBlock' => 'The block number the auction ends at.',
    'marketplace_bid.description' => 'The auction bid.',
    'marketplace_bid.field.bidder' => 'The account who placed the bid.',
    'marketplace_sale.description' => 'The listing sale.',
    'marketplace_state.description' => 'The state of the marketplace listing.',
    'marketplace_state.field.height' => 'The block height.',
];
