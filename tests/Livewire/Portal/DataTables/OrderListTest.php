<?php

namespace FluxErp\Tests\Livewire\Portal\DataTables;

use FluxErp\Livewire\Portal\DataTables\OrderList;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class OrderListTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(OrderList::class)
            ->assertStatus(200);
    }
}
