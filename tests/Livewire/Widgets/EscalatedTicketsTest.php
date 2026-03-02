<?php

use FluxErp\Livewire\Widgets\EscalatedTickets;
use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\Done;
use FluxErp\States\Ticket\Escalated;
use FluxErp\States\Ticket\WaitingForSupport;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(EscalatedTickets::class)
        ->assertOk();
});

test('shows only escalated tickets with count', function (): void {
    $escalated = Ticket::factory()->count(2)->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'state' => Escalated::class,
    ]);

    Ticket::factory()->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'state' => WaitingForSupport::class,
    ]);

    Ticket::factory()->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'state' => Done::class,
    ]);

    $component = Livewire::test(EscalatedTickets::class);

    $component->assertOk()
        ->assertSee($escalated->first()->title)
        ->assertSee($escalated->last()->title)
        ->assertSet('count', 2);
});

test('shows no tickets message when none escalated', function (): void {
    Livewire::test(EscalatedTickets::class)
        ->assertOk()
        ->assertSee(__('No escalated tickets'))
        ->assertSet('count', 0);
});
