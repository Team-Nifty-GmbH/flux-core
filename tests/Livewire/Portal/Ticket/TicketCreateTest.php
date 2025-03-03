<?php

namespace FluxErp\Tests\Livewire\Portal\Ticket;

use FluxErp\Livewire\Portal\Ticket\TicketCreate;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class TicketCreateTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(TicketCreate::class)
            ->assertStatus(200);
    }
}
