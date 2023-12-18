<?php

namespace Enjin\Platform\Marketplace\Events\Substrate\Marketplace;

use Enjin\Platform\Channels\PlatformAppChannel;
use Enjin\Platform\Events\PlatformBroadcastEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;

class ListingCreated extends PlatformBroadcastEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(Model $listing, Model $state)
    {
        parent::__construct();

        $this->broadcastData = [
            'listingId' => $listing->listing_chain_id,
            'seller' => $listing->seller->address,
            'makeAssetId' => [
                'collectionId' => $listing->make_collection_chain_id,
                'tokenId' => $listing->make_token_chain_id,
            ],
            'takeAssetId' => [
                'collectionId' => $listing->take_collection_chain_id,
                'tokenId' => $listing->take_token_chain_id,
            ],
            'amount' => $listing->amount,
            'price' => $listing->price,
            'minTakeValue' => $listing->min_take_value,
            'fee_side' => $listing->fee_side,
            'state' => $state->state,
            'creation_block' => $listing->creation_block,
            'deposit' => $listing->deposit,
            'salt' => $listing->salt,
            'type' => $listing->type,
            'start_block' => $listing->start_block,
            'end_block' => $listing->end_block,
            'amount_filled' => $listing->amount_filled,
        ];

        $this->broadcastChannels = [
            new Channel("listing;{$this->broadcastData['listingId']}"),
            new Channel($this->broadcastData['seller']),
            new Channel("collection;{$this->broadcastData['makeAssetId']['collectionId']}"),
            new Channel("token;{$this->broadcastData['makeAssetId']['tokenId']}"),
            new PlatformAppChannel(),
        ];
    }
}
