<?php

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\TicketsByCustomer;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Models\User;
use FluxErp\States\Ticket\WaitingForSupport;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TicketsByCustomer::class)
        ->assertOk();
});

test('shows top customers by ticket count with type breakdown', function (): void {
    $user2 = User::factory()->create(['is_active' => true, 'language_id' => $this->defaultLanguage->getKey()]);
    $ticketType = TicketType::factory()->create();

    Ticket::factory()->count(3)->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'ticket_type_id' => $ticketType->getKey(),
        'state' => WaitingForSupport::class,
    ]);

    Ticket::factory()->create([
        'authenticatable_type' => $user2->getMorphClass(),
        'authenticatable_id' => $user2->getKey(),
        'ticket_type_id' => $ticketType->getKey(),
        'state' => WaitingForSupport::class,
    ]);

    $component = Livewire::test(TicketsByCustomer::class)
        ->set('timeFrame', TimeFrameEnum::ThisMonth)
        ->call('calculateChart');

    $component->assertOk();

    $labels = $component->get('labels');
    $series = $component->get('series');
    $typeSeries = collect($series)->firstWhere('name', $ticketType->name);

    expect($labels)->toHaveCount(2)
        ->and($labels[0])->toBe($this->user->name)
        ->and($typeSeries)->not->toBeNull()
        ->and($typeSeries['data'][0])->toBe(3);
});

test('respects time frame', function (): void {
    Ticket::factory()->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'state' => WaitingForSupport::class,
        'created_at' => now()->subYear(),
    ]);

    $component = Livewire::test(TicketsByCustomer::class)
        ->set('timeFrame', TimeFrameEnum::ThisMonth)
        ->call('calculateChart');

    $series = $component->get('series');

    expect($series)->toBeEmpty();
});
