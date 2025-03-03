<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\SerialNumberList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class SerialNumberListTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(SerialNumberList::class)
            ->assertStatus(200);
    }
}
