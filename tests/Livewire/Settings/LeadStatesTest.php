<?php

use FluxErp\Livewire\Settings\LeadStates;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LeadStates::class)
        ->assertOk();
});

test('a validation error lands on the field the form bound it to', function (): void {
    Livewire::test(LeadStates::class)
        ->set('leadStateForm.name', null)
        ->call('save')
        ->assertHasErrors('leadStateForm.name');
});
