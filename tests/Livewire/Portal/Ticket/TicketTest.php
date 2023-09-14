<?php

namespace FluxErp\Tests\Livewire\Portal\Ticket;

use FluxErp\Livewire\Portal\Ticket\Ticket;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class TicketTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $tickets;

    public function setUp(): void
    {
        parent::setUp();

        $this->tickets = \FluxErp\Models\Ticket::factory()->count(2)->create([
            'authenticatable_type' => \FluxErp\Models\Address::class,
            'authenticatable_id' => $this->address->id,
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(Ticket::class, ['id' => $this->tickets->first()->id])
            ->assertStatus(200);
    }
}
