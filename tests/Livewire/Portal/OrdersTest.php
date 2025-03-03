<?php

namespace FluxErp\Tests\Livewire\Portal;

use FluxErp\Livewire\Portal\Orders;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class OrdersTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(Orders::class)
            ->assertStatus(200);
    }
}
