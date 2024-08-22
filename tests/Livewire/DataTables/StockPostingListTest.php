<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\StockPostingList;
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
