<?php

namespace Tests\Feature\Livewire\Product;

use FluxErp\Livewire\Product\StockPostingList;
use Livewire\Livewire;
use Tests\TestCase;

class StockPostingListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(StockPostingList::class)
            ->assertStatus(200);
    }
}
