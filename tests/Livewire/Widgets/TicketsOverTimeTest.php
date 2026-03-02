<?php

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\TicketsOverTime;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\States\Ticket\WaitingForSupport;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TicketsOverTime::class)
        ->assertOk();
});

test('shows ticket volume over time by type', function (): void {
    $ticketType = TicketType::factory()->create();

    Ticket::factory()->count(3)->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'ticket_type_id' => $ticketType->getKey(),
        'state' => WaitingForSupport::class,
        'created_at' => now(),
    ]);

    $component = Livewire::test(TicketsOverTime::class)
        ->set('timeFrame', TimeFrameEnum::ThisMonth)
        ->call('calculateChart');

    $component->assertOk();

    $series = $component->get('series');
    $typeSeries = collect($series)->firstWhere('name', $ticketType->name);

    expect($typeSeries)->not->toBeNull()
        ->and($typeSeries['data'])->toContain('3.00');
});

test('empty time frame shows no data', function (): void {
    $component = Livewire::test(TicketsOverTime::class)
        ->set('timeFrame', TimeFrameEnum::ThisMonth)
        ->call('calculateChart');

    $series = $component->get('series');
    $totalData = collect($series)->flatMap(fn ($s) => $s['data'])->sum();

    expect((float) $totalData)->toBe(0.0);
});
