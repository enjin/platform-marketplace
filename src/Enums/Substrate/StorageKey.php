<?php

namespace Enjin\Platform\Marketplace\Enums\Substrate;

use Enjin\Platform\Exceptions\PlatformException;
use Enjin\Platform\Traits\EnumExtensions;

enum StorageKey: string
{
    use EnumExtensions;

    case LISTINGS = '0xb8f32c9f36429933d924999a1b87423f202053cada0eb576e7ccf72ebc965b05';

    /**
     * Get the parser for this storage key.
     */
    public function parser(): string
    {
        return match ($this) {
            self::LISTINGS => 'listingsStorages',
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
