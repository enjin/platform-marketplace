<?php

namespace Enjin\Platform\Marketplace\Tests\Feature\GraphQL\Traits;

trait CreateListingParameters
{
    /**
     * Generate create listing params.
     */
    protected function generateParams(): array
    {
        return [
            'makeAssetId' => [
                'collectionId' => $this->collection->collection_chain_id,
                'tokenId' => ['integer' => $this->token->token_chain_id],
            ],
            'takeAssetId' => [
                'collectionId' => $this->collection->collection_chain_id,
                'tokenId' => ['integer' => $this->token->token_chain_id],
            ],
            'amount' => fake()->numberBetween(1, 1000),
            'price' => fake()->numberBetween(1, 1000),
            'salt' => fake()->text(10),
            'auctionData' => [
                'startBlock' => fake()->numberBetween(1011, 5000),
                'endBlock' => fake()->numberBetween(5001, 10000),
            ],
        ];
    }
}
