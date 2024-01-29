<?php

namespace Enjin\Platform\Marketplace\Rules;

use Closure;
use Enjin\Platform\Marketplace\Models\MarketplaceListing;
use Illuminate\Contracts\Validation\ValidationRule;

class ListingExists implements ValidationRule
{
    public function __construct(protected string $column = 'listing_chain_id')
    {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!MarketplaceListing::where($this->column, $value)->exists()) {
            $fail('validation.exists')->translate();
        }
    }
}
