<?php

namespace Tests\Feature\Livewire\DataTables;

use FluxErp\Livewire\DataTables\StockPostingList;
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
