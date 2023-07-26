<?php

namespace Enjin\Platform\Marketplace\GraphQL\Mutations;

use Closure;
use Enjin\Platform\GraphQL\Types\Input\Substrate\Traits\HasIdempotencyField;
use Enjin\Platform\Interfaces\PlatformBlockchainTransaction;
use Enjin\Platform\Marketplace\Rules\ListingNotCancelled;
use Enjin\Platform\Marketplace\Services\TransactionService;
use Enjin\Platform\Models\Transaction;
use Enjin\Platform\Rules\MaxBigInt;
use Enjin\Platform\Rules\MinBigInt;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Facades\DB;
use Rebing\GraphQL\Support\Facades\GraphQL;

class FillListingMutation extends Mutation implements PlatformBlockchainTransaction
{
    use HasIdempotencyField;

    /**
     * Get the mutation's attributes.
     */
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
    public function type(): Type
    {
        return GraphQL::type('Transaction!');
    }

    /**
     * Get the mutation's arguments definition.
     */
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
            ...$this->getIdempotencyField(),
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
        TransactionService $transaction
    ) {
        return Transaction::lazyLoadSelectFields(
            DB::transaction(fn () => $transaction->fillListing($args)),
            $resolveInfo
        );
    }

    /**
     * Get the mutation's request validation rules.
     */
    protected function rules(array $args = []): array
    {
        return [
            'listingId' => [
                'bail',
                'filled',
                'max:255',
                new ListingNotCancelled(),
            ],
            'amount' => [
                'bail',
                new MinBigInt(1),
                new MaxBigInt(),
            ],
        ];
    }
}
