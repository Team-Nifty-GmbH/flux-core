<?php

uses(FluxErp\Tests\Livewire\PortalBaseSetup::class);
use FluxErp\Livewire\Portal\Ticket\Comments;
use FluxErp\Models\Ticket;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->ticket = Ticket::factory()->create([
        'authenticatable_type' => $this->address->getMorphClass(),
        'authenticatable_id' => $this->address->id,
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(Comments::class, ['modelId' => $this->ticket->id])
        ->assertStatus(200);
});
