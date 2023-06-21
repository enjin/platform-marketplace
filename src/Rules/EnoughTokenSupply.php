<?php

namespace Enjin\Platform\Marketplace\Rules;

use Closure;
use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Traits\HasEncodableTokenId;
use Enjin\Platform\Marketplace\Enums\ListingState;
use Enjin\Platform\Marketplace\Models\MarketplaceListing;
use Enjin\Platform\Models\Token;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;

class EnoughTokenSupply implements DataAwareRule, ValidationRule
{
    use HasEncodableTokenId;

    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Set the data under validation.
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $collectionId = Arr::get($this->data, 'makeAssetId.collectionId');
        $tokenId = Arr::get($this->data, 'makeAssetId.tokenId');
        if (!$collectionId || !$tokenId) {
            return;
        }

        $tokenId = $this->encodeTokenId(Arr::get($this->data, 'makeAssetId'));
        $token = Token::whereHas('collection', fn ($query) => $query->where('collection_chain_id', $collectionId))
            ->firstWhere(['token_chain_id' => $tokenId]);
        if (!$token) {
            return;
        }

        $listing = MarketplaceListing::whereHas(
            'state',
            fn ($query) => $query->where('state', ListingState::ACTIVE->name)
        )->firstWhere([
            'make_collection_chain_id' => $collectionId,
            'make_token_chain_id' => $tokenId,
        ]);

        if ($token->supply < ($value + (int) $listing?->amount)) {
            $fail('enjin-platform-marketplace::validation.enough_token_supply')->translate();
        }
    }
}
