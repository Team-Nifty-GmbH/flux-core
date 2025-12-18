<?php

use FluxErp\Livewire\Ticket\Activities;
use FluxErp\Models\Ticket;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $ticket = Ticket::factory()->create([
        'authenticatable_id' => $this->user->id,
        'authenticatable_type' => $this->user->getMorphClass(),
    ]);
    Livewire::test(Activities::class, ['modelId' => $ticket->id])
        ->assertOk();
});
