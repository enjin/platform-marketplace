<?php

namespace Enjin\Platform\Marketplace\Rules;

use Closure;
use Enjin\Platform\Models\Block;
use Enjin\Platform\Services\Processor\Substrate\BlockProcessor;
use Illuminate\Contracts\Validation\ValidationRule;

class FutureBlock implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    #[\Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $latestBlock = app()->runningUnitTests()
           ? (int) Block::max('number')
           : (int) ((new BlockProcessor())->latestBlock() ?: Block::max('number'));

        $latestBlock += config('enjin-platform-marketplace.block_offset', 0);
        if ($latestBlock > $value) {
            $fail(__('enjin-platform-marketplace::validation.future_block', ['block' => $latestBlock]));
        }
    }
}
