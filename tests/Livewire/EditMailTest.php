<?php

use FluxErp\Livewire\EditMail;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::actingAs($this->user)
        ->test(EditMail::class)
        ->assertStatus(200);
});
