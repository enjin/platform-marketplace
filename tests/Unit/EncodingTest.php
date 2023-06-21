<?php

namespace Enjin\Platform\Marketplace\Tests\Unit;

use Enjin\Platform\Marketplace\Models\Substrate\AuctionDataParams;
use Enjin\Platform\Marketplace\Models\Substrate\MultiTokensTokenAssetIdParams;
use Enjin\Platform\Marketplace\Services\Processor\Substrate\Codec\Codec;
use Enjin\Platform\Marketplace\Tests\TestCase;

class EncodingTest extends TestCase
{
    protected Codec $codec;

    protected function setUp(): void
    {
        parent::setUp();
        $this->codec = new Codec();
    }

    public function test_it_can_encode_create_listing()
    {
        $asset = new MultiTokensTokenAssetIdParams(24016, 1);
        $data = $this->codec->encode()->createListing(
            $asset,
            $asset,
            1,
            1,
            'test',
            new AuctionDataParams(100, 1000)
        );

        $callIndex = $this->codec->encode()->callIndexes['Marketplace.create_listing'];
        $this->assertEquals(
            "0x{$callIndex}4277010004427701000404041074657374019101a10f",
            $data
        );
    }

    public function test_it_can_encode_cancel_listing()
    {
        $callIndex = $this->codec->encode()->callIndexes['Marketplace.cancel_listing'];
        $this->assertEquals(
            "0x{$callIndex}002ddf91ca0f13b03541dbddb3a008d8efc975b0044fde799ea7ffe33fdf57f7",
            $this->codec->encode()->cancelListing('0x002ddf91ca0f13b03541dbddb3a008d8efc975b0044fde799ea7ffe33fdf57f7')
        );
    }

    public function test_it_can_encode_fill_listing()
    {
        $callIndex = $this->codec->encode()->callIndexes['Marketplace.fill_listing'];
        $this->assertEquals(
            "0x{$callIndex}002ddf91ca0f13b03541dbddb3a008d8efc975b0044fde799ea7ffe33fdf57f7a10f",
            $this->codec->encode()->fillListing(
                '0x002ddf91ca0f13b03541dbddb3a008d8efc975b0044fde799ea7ffe33fdf57f7',
                1000
            )
        );
    }

    public function test_it_can_encode_finalize_auction()
    {
        $callIndex = $this->codec->encode()->callIndexes['Marketplace.finalize_auction'];
        $this->assertEquals(
            "0x{$callIndex}002ddf91ca0f13b03541dbddb3a008d8efc975b0044fde799ea7ffe33fdf57f7",
            $this->codec->encode()->finalizeAuction('0x002ddf91ca0f13b03541dbddb3a008d8efc975b0044fde799ea7ffe33fdf57f7')
        );
    }

    public function test_it_can_encode_place_bid()
    {
        $callIndex = $this->codec->encode()->callIndexes['Marketplace.place_bid'];
        $this->assertEquals(
            "0x{$callIndex}002ddf91ca0f13b03541dbddb3a008d8efc975b0044fde799ea7ffe33fdf57f7411f",
            $this->codec->encode()->placeBid(
                '0x002ddf91ca0f13b03541dbddb3a008d8efc975b0044fde799ea7ffe33fdf57f7',
                2000
            )
        );
    }

    public function test_it_can_encode_set_protocol_fee()
    {
        $callIndex = $this->codec->encode()->callIndexes['Marketplace.set_protocol_fee'];
        $this->assertEquals(
            "0x{$callIndex}01000000",
            $this->codec->encode()->setProtocolFee(1)
        );
    }
}
