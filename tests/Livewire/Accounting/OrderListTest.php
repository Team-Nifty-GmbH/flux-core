<?php

namespace FluxErp\Tests\Livewire\Accounting;

use FluxErp\Livewire\Accounting\OrderList;
use FluxErp\Tests\TestCase;
use Livewire\Livewire;

class OrderListTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(OrderList::class)
            ->assertStatus(200);
    }
}
