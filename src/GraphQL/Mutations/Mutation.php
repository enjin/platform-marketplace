<?php

namespace Enjin\Platform\Marketplace\GraphQL\Mutations;

use Enjin\Platform\Interfaces\PlatformGraphQlMutation;
use Enjin\Platform\Marketplace\GraphQL\Traits\InMarketplaceSchema;
use Illuminate\Support\Str;
use Rebing\GraphQL\Support\Mutation as GraphQlMutation;

abstract class Mutation extends GraphQlMutation implements PlatformGraphQlMutation
{
    use InMarketplaceSchema;

    /**
     * Adhoc rules.
     *
     * @var array
     */
    public static $adhocRules = [];

    /**
     * Get the blockchain method name from the graphql mutation name.
     */
    public function getMethodName(): string
    {
        return Str::camel($this->attributes()['name']);
    }

    /**
     * Get the graphql mutation name.
     */
    public function getMutationName(): string
    {
        return $this->attributes()['name'];
    }

    /**
     * Get validation rules.
     */
    public function getRules(array $arguments = []): array
    {
        return collect(parent::getRules($arguments))->mergeRecursive(static::$adhocRules)->all();
    }
}
