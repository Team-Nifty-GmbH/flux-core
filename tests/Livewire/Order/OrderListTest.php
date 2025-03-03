<?php

namespace FluxErp\Tests\Livewire\Order;

use FluxErp\Livewire\Order\OrderList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class OrderListTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(OrderList::class)
            ->assertStatus(200);
    }
}
