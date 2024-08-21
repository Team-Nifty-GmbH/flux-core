<?php

namespace FluxErp\Tests\Livewire\Product;

use FluxErp\Livewire\Product\StockPostingList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class StockPostingListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(StockPostingList::class)
            ->assertStatus(200);
    }
}
