<?php

use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\TicketResponseTime;
use FluxErp\Models\Comment;
use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use FluxErp\States\Ticket\Done;
use FluxErp\States\Ticket\WaitingForSupport;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TicketResponseTime::class)
        ->assertOk();
});

test('calculates first response time', function (): void {
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'state' => WaitingForSupport::class,
        'created_at' => now()->subHours(5),
    ]);

    $agent = User::factory()->create(['is_active' => true, 'language_id' => $this->defaultLanguage->getKey()]);

    Comment::factory()->create([
        'model_type' => morph_alias(Ticket::class),
        'model_id' => $ticket->getKey(),
        'created_by' => $agent->getMorphClass() . ':' . $agent->getKey(),
        'created_at' => now()->subHours(3),
    ]);

    $component = Livewire::test(TicketResponseTime::class)
        ->set('timeFrame', TimeFrameEnum::ThisMonth)
        ->call('calculateByTimeFrame');

    $component->assertOk();

    expect($component->get('firstResponseHours'))->toBeGreaterThan(0);
});

test('calculates resolution time', function (): void {
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => $this->user->getMorphClass(),
        'authenticatable_id' => $this->user->getKey(),
        'state' => WaitingForSupport::class,
        'created_at' => now()->subHours(10),
    ]);

    $ticket->state = Done::class;
    $ticket->save();

    $component = Livewire::test(TicketResponseTime::class)
        ->set('timeFrame', TimeFrameEnum::ThisMonth)
        ->call('calculateByTimeFrame');

    $component->assertOk();

    expect($component->get('resolutionHours'))->toBeGreaterThan(0);
});

test('shows no data when no tickets exist', function (): void {
    $component = Livewire::test(TicketResponseTime::class)
        ->set('timeFrame', TimeFrameEnum::ThisMonth)
        ->call('calculateByTimeFrame');

    $component->assertOk()
        ->assertSee(__('No data'));
});
