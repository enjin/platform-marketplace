<?php

namespace Enjin\Platform\Marketplace\Database\Factories;

use Enjin\Platform\Marketplace\Enums\FeeSide;
use Enjin\Platform\Marketplace\Enums\ListingType;
use Enjin\Platform\Marketplace\Models\MarketplaceListing;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarketplaceListingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = MarketplaceListing::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'listing_chain_id' => '0x' . fake()->regexify('[a-f0-9]{64}'),
            'make_collection_chain_id' => fake()->numberBetween(1, 100),
            'make_token_chain_id' => fake()->numberBetween(1, 100),
            'take_collection_chain_id' => fake()->numberBetween(1, 100),
            'take_token_chain_id' => fake()->numberBetween(1, 100),
            'amount' => fake()->numberBetween(1, 100),
            'price' => fake()->numberBetween(1, 100),
            'min_take_value' => fake()->numberBetween(1, 100),
            'fee_side' => FeeSide::caseNamesAsCollection()->random(),
            'creation_block' => fake()->numberBetween(1, 100),
            'deposit' => fake()->numberBetween(1, 100),
            'salt' => fake()->text(),
            'type' => $state = ListingType::caseNamesAsCollection()->random(),
            'start_block' => $state == ListingType::AUCTION->name ? fake()->numberBetween(1, 100) : null,
            'end_block' => $state == ListingType::AUCTION->name ? fake()->numberBetween(100, 200) : null,
            'amount_filled' =>  $state == ListingType::FIXED_PRICE->name ? fake()->numberBetween(1000, 2000) : null,
        ];
    }
}
