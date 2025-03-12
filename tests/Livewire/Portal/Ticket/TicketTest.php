<?php

namespace FluxErp\Tests\Livewire\Portal\Ticket;

use FluxErp\Livewire\Portal\Ticket\Ticket as TicketView;
use FluxErp\Models\Address;
use FluxErp\Models\Ticket;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class TicketTest extends BaseSetup
{
    private Ticket $tickets;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ticket = Ticket::factory()->create([
            'authenticatable_type' => morph_alias(Address::class),
            'authenticatable_id' => $this->address->id,
        ]);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(TicketView::class, ['id' => $this->ticket->id])
            ->assertStatus(200);
    }
}
