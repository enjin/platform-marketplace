<?php

namespace Enjin\Platform\Marketplace\Rules;

use Closure;
use Enjin\Platform\Marketplace\Enums\ListingState;
use Enjin\Platform\Marketplace\Models\Laravel\MarketplaceListing;
use Illuminate\Contracts\Validation\ValidationRule;

class ListingNotCancelled implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$listing = MarketplaceListing::where('listing_chain_id', $value)->with('state')->first()) {
            $fail('validation.exists')->translate();

            return;
        }

        if ($listing->state?->state === ListingState::CANCELLED->name) {
            $fail('enjin-platform-marketplace::validation.listing_not_cancelled')->translate();
        }
    }
}
