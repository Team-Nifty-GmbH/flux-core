<?php

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\TicketsOverTimeByState;
use FluxErp\Models\Ticket;
use FluxErp\States\Ticket\WaitingForSupport;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TicketsOverTimeByState::class)
        ->assertOk();
});

test('shows ticket volume over time by state', function (): void {
    Ticket::factory()->count(3)->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'state' => WaitingForSupport::class,
        'created_at' => now(),
    ]);

    $component = Livewire::test(TicketsOverTimeByState::class)
        ->set('timeFrame', TimeFrameEnum::ThisMonth)
        ->call('calculateChart');

    $component->assertOk();

    $series = $component->get('series');
    $stateSeries = collect($series)->firstWhere('name', 'Waiting For Support');

    expect($stateSeries)->not->toBeNull()
        ->and($stateSeries['data'])->toContain('3.00');
});

test('empty time frame shows no data', function (): void {
    $component = Livewire::test(TicketsOverTimeByState::class)
        ->set('timeFrame', TimeFrameEnum::ThisMonth)
        ->call('calculateChart');

    $series = $component->get('series');
    $totalData = collect($series)->flatMap(fn ($s) => $s['data'])->sum();

    expect((float) $totalData)->toBe(0.0);
});
