<?php

namespace Enjin\Platform\Marketplace\Events\Substrate\Marketplace;

use Enjin\Platform\Channels\PlatformAppChannel;
use Enjin\Platform\Events\PlatformBroadcastEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Model;

class AuctionFinalized extends PlatformBroadcastEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(Model $listing, Model $state, Model $sale, ?Model $transaction = null)
    {
        parent::__construct();

        $this->model = $sale;

        $this->broadcastData = [
            'idempotencyKey' => $transaction?->idempotency_key,
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
            'bidder' => $sale->bidder->address,
            'price' => $sale->price,
            'amount' => $sale->amount,
        ];

        $this->broadcastChannels = [
            new Channel("listing;{$this->broadcastData['listingId']}"),
            new Channel($this->broadcastData['seller']),
            new Channel($this->broadcastData['bidder']),
            new Channel("collection;{$this->broadcastData['makeAssetId']['collectionId']}"),
            new Channel("token;{$this->broadcastData['makeAssetId']['tokenId']}"),
            new PlatformAppChannel(),
        ];
    }
}
