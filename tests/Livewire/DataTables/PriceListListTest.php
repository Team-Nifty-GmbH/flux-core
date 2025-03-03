<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\PriceListList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class PriceListListTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(PriceListList::class)
            ->assertStatus(200);
    }
}
