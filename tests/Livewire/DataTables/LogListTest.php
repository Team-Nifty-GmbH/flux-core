<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\LogList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class LogListTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(LogList::class)
            ->assertStatus(200);
    }
}
