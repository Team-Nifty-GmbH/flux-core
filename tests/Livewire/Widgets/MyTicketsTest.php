<?php

namespace FluxErp\Tests\Livewire\Widgets;

use FluxErp\Livewire\Widgets\MyTickets;
use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\Done;
use FluxErp\States\Ticket\WaitingForSupport;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Str;
use Livewire\Livewire;

class MyTicketsTest extends BaseSetup
{
    public function test_renders_successfully(): void
    {
        $tickets = Ticket::factory()
            ->count(3)
            ->create([
                'authenticatable_type' => $this->user->getMorphClass(),
                'authenticatable_id' => $this->user->getKey(),
                'title' => fn () => Str::uuid(),
                'state' => WaitingForSupport::class,
            ]);
        $tickets->take(2)->each(fn (Ticket $ticket) => $ticket->users()->attach($this->user));
        $tickets->get(1)->state = Done::class;
        $tickets->get(1)->save();

        Livewire::test(MyTickets::class)
            ->assertStatus(200)
            ->assertSee($tickets->get(0)->title)
            ->assertDontSee($tickets->get(1)->title)
            ->assertDontSee($tickets->get(2)->title);
    }
}
