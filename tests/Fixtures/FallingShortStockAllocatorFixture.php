<?php

namespace FluxErp\Tests\Fixtures;

use FluxErp\Support\Stock\StockAllocator;
use Illuminate\Support\Collection;

class FallingShortStockAllocatorFixture extends StockAllocator
{
    public function allocate(string|int|float $amount): Collection
    {
        return parent::allocate(bcsub((string) $amount, '1', 10));
    }
}
