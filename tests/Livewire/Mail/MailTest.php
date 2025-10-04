<?php

use FluxErp\Livewire\Mail\Mail;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::actingAs($this->user)
        ->test(Mail::class)
        ->assertOk();
});
