<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Ticket\Activities;
use FluxErp\Models\Ticket;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->ticket = Ticket::factory()->create([
        'authenticatable_id' => $this->user->id,
        'authenticatable_type' => $this->user->getMorphClass(),
    ]);
});

test('renders successfully', function (): void {
    Livewire::test(Activities::class, ['modelId' => $this->ticket->id])
        ->assertStatus(200);
});
