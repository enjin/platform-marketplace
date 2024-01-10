<?php

namespace Enjin\Platform\Marketplace\Enums\Substrate;

use Enjin\Platform\Exceptions\PlatformException;

class StorageKey
{
    public function __construct(public StorageType $type, public string $value)
    {
    }

    public static function listings(?string $value = null): self
    {
        return new self(StorageType::LISTINGS, $value ?? '0xb8f32c9f36429933d924999a1b87423f202053cada0eb576e7ccf72ebc965b05');
    }

    /**
     * Get the parser for this storage key.
     */
    public function parser(): string
    {
        return match ($this->type) {
            StorageType::LISTINGS => 'listingsStorages',
            default => throw new PlatformException('No parser for this storage key.'),
        };
    }

    /**
     * The facade for the parser.
     */
    public function parserFacade(): string
    {
        return '\Facades\Enjin\Platform\Marketplace\Services\Processor\Substrate\Parser';
    }
}
