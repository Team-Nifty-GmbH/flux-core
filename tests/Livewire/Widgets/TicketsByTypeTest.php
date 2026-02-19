<?php

use FluxErp\Livewire\Widgets\TicketsByType;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\States\Ticket\Done;
use FluxErp\States\Ticket\WaitingForSupport;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TicketsByType::class)
        ->assertOk();
});

test('shows distribution by ticket type excluding end states', function (): void {
    $types = TicketType::factory()->count(2)->create();

    Ticket::factory()->count(3)->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'ticket_type_id' => $types->first()->getKey(),
        'state' => WaitingForSupport::class,
    ]);

    Ticket::factory()->count(2)->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'ticket_type_id' => $types->last()->getKey(),
        'state' => WaitingForSupport::class,
    ]);

    Ticket::factory()->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'ticket_type_id' => null,
        'state' => WaitingForSupport::class,
    ]);

    Ticket::factory()->count(2)->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'ticket_type_id' => $types->first()->getKey(),
        'state' => Done::class,
    ]);

    $component = Livewire::test(TicketsByType::class);
    $component->assertOk();

    $series = $component->get('series');
    $labels = $component->get('labels');

    expect(array_sum($series))->toBe(6)
        ->and($labels)->toContain($types->first()->name)
        ->and($labels)->toContain($types->last()->name);
});
