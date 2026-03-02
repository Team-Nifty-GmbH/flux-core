<?php

use FluxErp\Livewire\Widgets\TicketsPerUser;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\States\Ticket\Done;
use FluxErp\States\Ticket\InProgress;
use FluxErp\States\Ticket\WaitingForSupport;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TicketsPerUser::class)
        ->assertOk();
});

test('shows open tickets per agent by state', function (): void {
    $user2 = User::factory()->create(['is_active' => true, 'language_id' => $this->defaultLanguage->getKey()]);

    $tickets1 = Ticket::factory()->count(3)->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'state' => WaitingForSupport::class,
    ]);
    $tickets1->each(fn (Ticket $ticket) => $ticket->users()->attach($this->user));

    $tickets2 = Ticket::factory()->count(2)->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'state' => InProgress::class,
    ]);
    $tickets2->each(fn (Ticket $ticket) => $ticket->users()->attach($this->user));

    $tickets3 = Ticket::factory()->count(1)->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'state' => WaitingForSupport::class,
    ]);
    $tickets3->each(fn (Ticket $ticket) => $ticket->users()->attach($user2));

    $doneTicket = Ticket::factory()->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'state' => Done::class,
    ]);
    $doneTicket->users()->attach($this->user);

    $component = Livewire::test(TicketsPerUser::class);

    $component->assertOk();

    $labels = $component->get('labels');
    $series = $component->get('series');

    $waitingSeries = collect($series)->firstWhere('name', 'Waiting For Support');
    $inProgressSeries = collect($series)->firstWhere('name', 'In Progress');

    expect($labels)->toContain($this->user->name)
        ->and($labels)->toContain($user2->name)
        ->and($labels[0])->toBe($this->user->name)
        ->and($waitingSeries)->not->toBeNull()
        ->and($waitingSeries['data'][0])->toBe(3)
        ->and($waitingSeries['data'][1])->toBe(1)
        ->and($inProgressSeries)->not->toBeNull()
        ->and($inProgressSeries['data'][0])->toBe(2);
});
