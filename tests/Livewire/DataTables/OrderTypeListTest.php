<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\OrderTypeList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class OrderTypeListTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(OrderTypeList::class)
            ->assertStatus(200);
    }
}
