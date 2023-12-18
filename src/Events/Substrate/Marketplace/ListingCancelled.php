<?php

namespace Enjin\Platform\Marketplace\Events\Substrate\Marketplace;

use Enjin\Platform\Channels\PlatformAppChannel;
use Enjin\Platform\Events\PlatformBroadcastEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;

class ListingCancelled extends PlatformBroadcastEvent
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
            'state' => $state->state,
            'height' => $state->height,
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
