<?php

namespace FluxErp\Tests\Livewire\Portal\DataTables;

use FluxErp\Livewire\Portal\DataTables\TicketList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class TicketListTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(TicketList::class)
            ->assertStatus(200);
    }
}
