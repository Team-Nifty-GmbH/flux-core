<?php

use FluxErp\Livewire\Ticket\Comments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::actingAs($this->user)
        ->test(Comments::class)
        ->assertOk();
});
