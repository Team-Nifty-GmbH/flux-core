<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\TicketTypeEdit;
use FluxErp\Models\TicketType;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('create new ticket type', function (): void {
    Livewire::test(TicketTypeEdit::class)
        ->set('ticketType.name', $ticketTypeName = Str::uuid())
        ->set('ticketType.model_type', 'order')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatchedTo('settings.ticket-types', 'closeModal')
        ->assertToastNotification(type: 'success');

    $this->assertDatabaseHas('ticket_types', [
        'name' => $ticketTypeName,
        'model_type' => 'order',
    ]);
});

test('delete ticket type', function (): void {
    $ticketType = TicketType::factory()->create();

    Livewire::test(TicketTypeEdit::class)
        ->call('show', $ticketType->toArray())
        ->call('delete')
        ->assertHasNoErrors()
        ->assertDispatchedTo(
            'settings.ticket-types',
            'closeModal',
            ['id' => $ticketType->id]
        );

    $this->assertSoftDeleted('ticket_types', [
        'id' => $ticketType->id,
    ]);
});

test('edit ticket type', function (): void {
    $ticketType = TicketType::factory()->create();

    Livewire::test(TicketTypeEdit::class)
        ->call('show', $ticketType->toArray())
        ->set('ticketType.name', $ticketTypeName = Str::uuid())
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatchedTo('settings.ticket-types', 'closeModal')
        ->assertToastNotification(type: 'success');

    $this->assertDatabaseHas('ticket_types', [
        'id' => $ticketType->id,
        'name' => $ticketTypeName,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(TicketTypeEdit::class)
        ->assertStatus(200);
});
