<?php

namespace Enjin\Platform\Marketplace\Services\Processor\Substrate\Codec;

use Enjin\Platform\Marketplace\Services\Processor\Substrate\Codec\Decoder as MarketplaceDecoder;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Codec\Encoder as MarketplaceEncoder;
use Enjin\Platform\Services\Processor\Substrate\Codec\Codec as BaseCodec;

class Codec extends BaseCodec
{
    /**
     * Creates the codec instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->encoder = new MarketplaceEncoder($this->scaleInstance);
        $this->decoder = new MarketplaceDecoder($this->scaleInstance);
    }

    /**
     * Returns the marketplace encoder.
     */
    public function encoder(): Encoder
    {
        return $this->encoder;
    }

    /**
     * Returns the marketplace decoder.
     */
    public function decoder(): Decoder
    {
        return $this->decoder;
    }
}
