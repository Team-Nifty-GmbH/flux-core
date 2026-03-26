<?php

use FluxErp\Livewire\Widgets\TicketsByState;
use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\Done;
use FluxErp\States\Ticket\Escalated;
use FluxErp\States\Ticket\InProgress;
use FluxErp\States\Ticket\WaitingForSupport;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TicketsByState::class)
        ->assertOk();
});

test('shows distribution by state excluding end states', function (): void {
    Ticket::factory()->count(3)->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'state' => WaitingForSupport::class,
    ]);

    Ticket::factory()->count(2)->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'state' => InProgress::class,
    ]);

    Ticket::factory()->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'state' => Escalated::class,
    ]);

    Ticket::factory()->count(2)->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'state' => Done::class,
    ]);

    $component = Livewire::test(TicketsByState::class);

    $component->assertOk();

    $series = $component->get('series');
    $labels = $component->get('labels');

    expect($series)->toHaveCount(3)
        ->and(array_sum($series))->toBe(6)
        ->and($labels)->not->toContain(__('Done'))
        ->and($labels)->not->toContain(__('Closed'));
});
