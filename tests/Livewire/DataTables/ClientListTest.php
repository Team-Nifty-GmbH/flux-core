<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\ClientList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class ClientListTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(ClientList::class)
            ->assertStatus(200);
    }
}
