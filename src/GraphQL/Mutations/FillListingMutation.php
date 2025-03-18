<?php

namespace Enjin\Platform\Marketplace\GraphQL\Mutations;

use Closure;
use Enjin\BlockchainTools\HexConverter;
use Enjin\Platform\Facades\TransactionSerializer;
use Enjin\Platform\GraphQL\Schemas\Primary\Substrate\Traits\StoresTransactions;
use Enjin\Platform\GraphQL\Schemas\Primary\Traits\HasSkippableRules;
use Enjin\Platform\GraphQL\Schemas\Primary\Traits\HasTransactionDeposit;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasIdempotencyField;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasSigningAccountField;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasSimulateField;
use Enjin\Platform\Interfaces\PlatformBlockchainTransaction;
use Enjin\Platform\Marketplace\Rules\ListingNotCancelled;
use Facades\Enjin\Platform\Marketplace\Services\MarketplaceService;
use Enjin\Platform\Models\Transaction;
use Enjin\Platform\Rules\MaxBigInt;
use Enjin\Platform\Rules\MinBigInt;
use Enjin\Platform\Rules\ValidHex;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Rebing\GraphQL\Support\Facades\GraphQL;

class FillListingMutation extends Mutation implements PlatformBlockchainTransaction
{
    use HasIdempotencyField;
    use HasSigningAccountField;
    use HasSimulateField;
    use HasSkippableRules;
    use HasTransactionDeposit;
    use StoresTransactions;

    /**
     * Get the mutation's attributes.
     */
    #[\Override]
    public function attributes(): array
    {
        return [
            'name' => 'FillListing',
            'description' => __('enjin-platform-marketplace::mutation.fill_listing.description'),
        ];
    }

    /**
     * Get the mutation's return type.
     */
    #[\Override]
    public function type(): Type
    {
        return GraphQL::type('Transaction!');
    }

    /**
     * Get the mutation's arguments definition.
     */
    #[\Override]
    public function args(): array
    {
        return [
            'listingId' => [
                'type' => GraphQL::type('String!'),
                'description' => __('enjin-platform-marketplace::type.marketplace_listing.field.listingId'),
            ],
            'amount' => [
                'type' => GraphQL::type('BigInt!'),
                'description' => __('enjin-platform-marketplace::type.marketplace_listing.field.amount'),
            ],
            ...$this->getSigningAccountField(),
            ...$this->getIdempotencyField(),
            ...$this->getSimulateField(),
            ...$this->getSkipValidationField(),
        ];
    }

    /**
     * Resolve the mutation's request.
     */
    public function resolve(
        $root,
        array $args,
        $context,
        ResolveInfo $resolveInfo,
        Closure $getSelectFields,
    ) {
        $encodedData = TransactionSerializer::encode($this->getMutationName() . (currentSpec() >= 1020 ? '' : 'V1013'), static::getEncodableParams(...$args));

        return Transaction::lazyLoadSelectFields(
            DB::transaction(fn () => $this->storeTransaction($args, $encodedData)),
            $resolveInfo
        );
    }

    #[\Override]
    public static function getEncodableParams(...$params): array
    {
        $count = MarketplaceService::getRoyaltyBeneficiaryCount($listingId = Arr::get($params, 'listingId'));

        return [
            'listingId' => HexConverter::unPrefix($listingId),
            'amount' => gmp_init(Arr::get($params, 'amount', 0)),
            'royaltyBeneficiaryCount' => $count,
        ];
    }

    /**
     * Get the common rules.
     */
    protected function rulesCommon(array $args): array
    {
        return [
            'amount' => [
                'bail',
                new MinBigInt(1),
                new MaxBigInt(),
            ],
        ];
    }

    /**
     * Get the mutation's validation rules.
     */
    protected function rulesWithValidation(array $args): array
    {
        return [
            'listingId' => [
                'bail',
                'filled',
                'max:255',
                new ListingNotCancelled(),
            ],
        ];
    }

    /**
     * Get the mutation's validation rules without DB rules.
     */
    protected function rulesWithoutValidation(array $args): array
    {
        return [
            'listingId' => [
                'bail',
                'filled',
                'max:255',
                new ValidHex(32),
            ],
        ];
    }
}
