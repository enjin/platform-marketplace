<?php

namespace Enjin\Platform\Marketplace\Database\Factories;

use Enjin\Platform\Marketplace\Enums\ListingState;
use Enjin\Platform\Marketplace\Models\MarketplaceState;
use Illuminate\Database\Eloquent\Factories\Factory;

class MarketplaceStateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = MarketplaceState::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'state' => ListingState::caseNamesAsCollection()->random(),
            'height' => fake()->numberBetween(1, 100),
        ];
    }
}
