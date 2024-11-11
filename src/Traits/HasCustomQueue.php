<?php

namespace Enjin\Platform\Marketplace\Traits;

trait HasCustomQueue
{
    protected function setQueue(): void
    {
        $this->onQueue(config('enjin-platform-marketplace.queue'));
    }
}
