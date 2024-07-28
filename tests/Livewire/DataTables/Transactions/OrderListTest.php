<?php

namespace Tests\Feature\Livewire\DataTables\Transactions;

use FluxErp\Livewire\DataTables\Transactions\OrderList;
use Livewire\Livewire;
use Tests\TestCase;

class OrderListTest extends TestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(OrderList::class)
            ->assertStatus(200);
    }
}
