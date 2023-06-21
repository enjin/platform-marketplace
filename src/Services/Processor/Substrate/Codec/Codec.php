<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Codec;

use Enjin\Platform\Marketplace\Services\Processor\Substrate\Codec\Decoder as MarketplaceDecoder;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Codec\Encoder as MarketplaceEncoder;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec as BaseCodec;

class Codec extends BaseCodec
{
    protected Encoder $marketplaceEncoder;
    protected Decoder $marketplaceDecoder;

    /**
     * Creates the codec instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->marketplaceEncoder = new MarketplaceEncoder($this->scaleInstance);
        $this->marketplaceDecoder = new MarketplaceDecoder($this->scaleInstance);
    }

    /**
     * Returns the marketplace encoder.
     */
    public function encode(): Encoder
    {
        return $this->marketplaceEncoder;
    }

    /**
     * Returns the marketplace decoder.
     */
    public function decode(): Decoder
    {
        return $this->marketplaceDecoder;
    }
}
