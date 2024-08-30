<?php

namespace FluxErp\Tests\Livewire\Portal\Ticket;

use FluxErp\Livewire\Portal\Ticket\Tickets;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class TicketsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_renders_successfully()
    {
        Livewire::test(Tickets::class)
            ->assertStatus(200);
    }
}
