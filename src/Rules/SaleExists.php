<?php

namespace Enjin\Platform\Marketplace\Rules;

use Closure;
use Enjin\Platform\Marketplace\Models\MarketplaceSale;
use Illuminate\Contracts\Validation\ValidationRule;

class SaleExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    #[\Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!MarketplaceSale::where('id', $value)->exists()) {
            $fail('validation.exists')->translate();
        }
    }
}
