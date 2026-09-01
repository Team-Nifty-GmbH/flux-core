<?php

use FluxErp\Livewire\Mail\Mail;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::actingAs($this->user)
        ->test(Mail::class)
        ->assertOk();
});

test('compose mail dispatches create event to edit mail', function (): void {
    Livewire::actingAs($this->user)
        ->test(Mail::class)
        ->call('composeMail')
        ->assertDispatchedTo('edit-mail', 'create', values: []);
});
