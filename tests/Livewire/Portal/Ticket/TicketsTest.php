<?php

namespace FluxErp\Tests\Livewire\Portal\Ticket;

use FluxErp\Livewire\Portal\Ticket\Tickets;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class TicketsTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        Livewire::test(Tickets::class)
            ->assertStatus(200);
    }
}
