<?php

namespace Enjin\Platform\Marketplace\Models\Substrate;

class AuctionDataParams
{
    /**
     * Create a new instance of the model.
     */
    public function __construct(
        public int $startBlock,
        public int $endBlock
    ) {}

    /**
     * Convert the object to encodable formatted array.
     */
    public function toEncodable(): array
    {
        return ['startBlock' => $this->startBlock, 'endBlock' => $this->endBlock];
    }
}
