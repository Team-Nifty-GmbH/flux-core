<?php

namespace FluxErp\Tests\Livewire\DataTables;

use FluxErp\Livewire\DataTables\TicketList;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class TicketListTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(TicketList::class)
            ->assertStatus(200);
    }
}
