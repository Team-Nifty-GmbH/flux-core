<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\OrderPositionList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class OrderPositionListTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(OrderPositionList::class)
            ->assertStatus(200);
    }
}
