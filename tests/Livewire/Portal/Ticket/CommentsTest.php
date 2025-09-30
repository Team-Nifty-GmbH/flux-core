<?php

use FluxErp\Livewire\Portal\Ticket\Comments;
use FluxErp\Models\Ticket;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $ticket = Ticket::factory()->create([
        'authenticatable_type' => $this->address->getMorphClass(),
        'authenticatable_id' => $this->address->id,
    ]);
    Livewire::test(Comments::class, ['modelId' => $ticket->id])
        ->assertOk();
});
