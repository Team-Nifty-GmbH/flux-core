<?php

namespace FluxErp\Tests\Livewire\Ticket;

use FluxErp\Livewire\Ticket\Ticket as TicketView;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class TicketTest extends BaseSetup
{
    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ticket = Ticket::factory()->create([
            'authenticatable_type' => morph_alias(User::class),
            'authenticatable_id' => $this->user->id,
        ]);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(TicketView::class, ['id' => $this->ticket->id])
            ->assertStatus(200);
    }

    public function test_switch_tabs(): void
    {
        $component = Livewire::test(TicketView::class, ['id' => $this->ticket->id]);

        foreach (Livewire::new(TicketView::class)->getTabs() as $tab) {
            $component
                ->set('tab', $tab->component)
                ->assertStatus(200);

            if ($tab->isLivewireComponent) {
                $component->assertSeeLivewire($tab->component);
            }
        }
    }
}
