<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\TicketTypeEdit;
use FluxErp\Models\TicketType;
use FluxErp\Tests\TestCase;
use Illuminate\Support\Str;
use Livewire\Livewire;

class TicketTypeEditTest extends TestCase
{
    public function test_create_new_ticket_type(): void
    {
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
    }

    public function test_delete_ticket_type(): void
    {
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
    }

    public function test_edit_ticket_type(): void
    {
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
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(TicketTypeEdit::class)
            ->assertStatus(200);
    }
}
