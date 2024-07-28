<?php

namespace Tests\Feature\Livewire\Accounting;

use FluxErp\Livewire\Accounting\OrderList;
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
