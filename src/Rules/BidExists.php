<?php

namespace Enjin\Platform\Marketplace\Rules;

use Closure;
use Enjin\Platform\Marketplace\Models\MarketplaceBid;
use Illuminate\Contracts\Validation\ValidationRule;

class BidExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    #[\Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!MarketplaceBid::where('id', $value)->exists()) {
            $fail('validation.exists')->translate();
        }
    }
}
