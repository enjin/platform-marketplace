<?php

namespace Enjin\Platform\Marketplace\Tests\Feature\GraphQL\Mutations;

use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Traits\HasEncodableTokenId;
use Enjin\Platform\Marketplace\GraphQL\Mutations\CreateListingMutation;
use Enjin\Platform\Marketplace\Models\Substrate\AuctionDataParams;
use Enjin\Platform\Marketplace\Models\Substrate\MultiTokensTokenAssetIdParams;
use Enjin\Platform\Marketplace\Tests\Feature\GraphQL\TestCaseGraphQL;
use Enjin\Platform\Models\Block;
use Enjin\Platform\Support\Hex;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CreateListingTest extends TestCaseGraphQL
{
    use HasEncodableTokenId;

    /**
     * The graphql method.
     */
    protected string $method = 'CreateListing';

    /**
     * Setup test case.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Block::updateOrCreate(['number' => 1000]);
    }

    public function test_it_can_create_listing(): void
    {
        $response = $this->graphql(
            $this->method,
            $params = $this->generateParams()
        );

        $params['makeAssetId'] = new MultiTokensTokenAssetIdParams(
            Arr::get($params, 'makeAssetId.collectionId'),
            $this->encodeTokenId(Arr::get($params, 'makeAssetId'))
        );
        $params['takeAssetId'] = new MultiTokensTokenAssetIdParams(
            Arr::get($params, 'takeAssetId.collectionId'),
            $this->encodeTokenId(Arr::get($params, 'takeAssetId'))
        );
        $params['auctionData'] = ($data = Arr::get($params, 'auctionData'))
            ? new AuctionDataParams(Arr::get($params, 'auctionData.startBlock'), Arr::get($params, 'auctionData.endBlock'))
            : null;

        $this->assertEquals(
            $response['encodedData'],
            TransactionSerializer::encode($this->method, CreateListingMutation::getEncodableParams(...$params))
        );
    }

    public function test_it_will_fail_with_invalid_parameter_account(): void
    {
        $data = $this->generateParams();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['account' => '']),
            true
        );
        $this->assertArraySubset(
            ['account' => ['The account field must have a value.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['account' => Str::random(300)]),
            true
        );
        $this->assertArraySubset(
            ['account' => ['The account field must not be greater than 255 characters.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['account' => 'Invalid']),
            true
        );
        $this->assertArraySubset(
            ['account' => ['The account is not a valid substrate address.']],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_make_asset_id(): void
    {
        $data = $this->generateParams();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['makeAssetId' => null]),
            true
        );
        $this->assertEquals(
            'Variable "$makeAssetId" of non-null type "MultiTokenIdInput!" must not be null.',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['makeAssetId' => ['collectionId' => null, 'tokenId' => null]]),
            true
        );
        $this->assertStringContainsString(
            'Variable "$makeAssetId" got invalid value null at "makeAssetId.collectionId"; Expected non-nullable type "BigInt!" not to be null.',
            $response['errors'][0]['message']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, [
                'makeAssetId' => [
                    'collectionId' => fake()->numberBetween(3000, 4000),
                    'tokenId' => ['integer' =>fake()->numberBetween(3000, 4000)],
                ],
            ]),
            true
        );
        $this->assertArraySubset(
            [
                'makeAssetId.collectionId' => ['The selected make asset id.collection id is invalid.'],
            ],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, [
                'makeAssetId' => [
                    'collectionId' => Hex::MAX_UINT256 + 1,
                    'tokenId' => ['integer' =>Hex::MAX_UINT256 + 1],
                ],
            ]),
            true
        );
        $this->assertStringContainsString(
            'Variable "$makeAssetId" got invalid value 1.1579208923732E+77 at "makeAssetId.collectionId"; Cannot represent following value as uint256: 1.1579208923732E+77',
            $response['errors'][0]['message']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_take_asset_id(): void
    {
        $data = $this->generateParams();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['takeAssetId' => null]),
            true
        );
        $this->assertEquals(
            'Variable "$takeAssetId" of non-null type "MultiTokenIdInput!" must not be null.',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['takeAssetId' => ['collectionId' => null, 'tokenId' => null]]),
            true
        );
        $this->assertStringContainsString(
            'Variable "$takeAssetId" got invalid value null at "takeAssetId.collectionId"; Expected non-nullable type "BigInt!" not to be null.',
            $response['errors'][0]['message']
        );
        $this->assertStringContainsString(
            'Variable "$takeAssetId" got invalid value null at "takeAssetId.tokenId"; Expected non-nullable type "EncodableTokenIdInput!" not to be null.',
            $response['errors'][1]['message']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, [
                'takeAssetId' => [
                    'collectionId' => fake()->numberBetween(3000, 4000),
                    'tokenId' => ['integer' => fake()->numberBetween(3000, 4000)],
                ],
            ]),
            true
        );
        $this->assertArraySubset(
            [
                'takeAssetId.collectionId' => ['The selected take asset id.collection id is invalid.'],
                'takeAssetId' => ['The take asset id does not exist in the specified collection.'],
            ],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, [
                'takeAssetId' => [
                    'collectionId' => Hex::MAX_UINT256 + 1,
                    'tokenId' => Hex::MAX_UINT256 + 1,
                ],
            ]),
            true
        );
        $this->assertStringContainsString(
            'Variable "$takeAssetId" got invalid value 1.1579208923732E+77 at "takeAssetId.collectionId"; Cannot represent following value as uint256: 1.1579208923732E+77',
            $response['errors'][0]['message']
        );
        $this->assertStringContainsString(
            'Variable "$takeAssetId" got invalid value 1.1579208923732E+77 at "takeAssetId.tokenId"; Expected type "EncodableTokenIdInput" to be an object.',
            $response['errors'][1]['message']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_amount(): void
    {
        $data = $this->generateParams();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['amount' => null]),
            true
        );
        $this->assertEquals(
            'Variable "$amount" of non-null type "BigInt!" must not be null.',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['amount' => '']),
            true
        );
        $this->assertStringContainsString(
            'Variable "$amount" got invalid value (empty string); Cannot represent following value as uint256: (empty string)',
            $response['errors'][0]['message']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['amount' => Hex::MAX_UINT256 + 1]),
            true
        );
        $this->assertStringContainsString(
            'Variable "$amount" got invalid value 1.1579208923732E+77; Cannot represent following value as uint256: 1.1579208923732E+77',
            $response['errors'][0]['message']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['amount' => $this->token->supply + 1]),
            true
        );
        $this->assertArraySubset(
            ['amount' => ['The token supply is not enough.']],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_price(): void
    {
        $data = $this->generateParams();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['price' => null]),
            true
        );
        $this->assertEquals(
            'Variable "$price" of non-null type "BigInt!" must not be null.',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['price' => '']),
            true
        );
        $this->assertStringContainsString(
            'Cannot represent following value as uint256: (empty string)',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['price' => Hex::MAX_UINT256 + 1]),
            true
        );
        $this->assertStringContainsString(
            'Variable "$price" got invalid value 1.1579208923732E+77; Cannot represent following value as uint256: 1.1579208923732E+77',
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_salt(): void
    {
        $data = $this->generateParams();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['salt' => null]),
            true
        );
        $this->assertEquals(
            'Variable "$salt" of non-null type "String!" must not be null.',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['salt' => '']),
            true
        );
        $this->assertArraySubset(
            ['salt' => ['The salt field must have a value.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['salt' => Str::random(300)]),
            true
        );
        $this->assertArraySubset(
            ['salt' => ['The salt field must not be greater than 255 characters.']],
            $response['error']
        );
    }

    public function test_it_will_fail_with_invalid_parameter_auction_data(): void
    {
        $data = $this->generateParams();
        $response = $this->graphql(
            $this->method,
            array_merge($data, ['auctionData' => null]),
            true
        );
        $this->assertNotEmpty($response['data']);

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['auctionData' => [
                'startBlock' => fake()->numberBetween(1011, 2000),
                'endBlock' => null,
            ]]),
            true
        );
        $this->assertStringContainsString(
            'Variable "$auctionData" got invalid value null at "auctionData.endBlock"; Expected non-nullable type "Int!" not to be null.',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['auctionData' => [
                'startBlock' => null,
                'endBlock' => fake()->numberBetween(1011, 2000),
            ]]),
            true
        );
        $this->assertStringContainsString(
            'Variable "$auctionData" got invalid value null at "auctionData.startBlock"; Expected non-nullable type "Int!" not to be null.',
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['auctionData' => [
                'startBlock' => Hex::MAX_UINT128 + 1,
                'endBlock' => Hex::MAX_UINT128 + 1,
            ]]),
            true
        );
        $this->assertStringContainsString(
            'Variable "$auctionData" got invalid value 3.4028236692094E+38 at "auctionData.startBlock"; Int cannot represent non 32-bit signed integer value: 3.4028236692094E+38',
            $response['errors'][0]['message']
        );
        $this->assertStringContainsString(
            'Variable "$auctionData" got invalid value 3.4028236692094E+38 at "auctionData.endBlock"; Int cannot represent non 32-bit signed integer value: 3.4028236692094E+38',
            $response['errors'][1]['message']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['auctionData' => [
                'startBlock' => 1,
                'endBlock' => 2,
            ]]),
            true
        );
        $this->assertArraySubset(
            ['auctionData.startBlock' => ['The auction data.start block must be at least 1010.']],
            $response['error']
        );

        $response = $this->graphql(
            $this->method,
            array_merge($data, ['auctionData' => [
                'startBlock' => 1012,
                'endBlock' => 1011,
            ]]),
            true
        );
        $this->assertArraySubset(
            [
                'auctionData.startBlock' => ['The auction data.start block field must be less than or equal to 1011.'],
                'auctionData.endBlock' => ['The auction data.end block field must be greater than 1012.'],
            ],
            $response['error']
        );
    }
}
