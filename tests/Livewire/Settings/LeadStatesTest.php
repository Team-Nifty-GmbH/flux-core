<?php

use FluxErp\Livewire\Settings\LeadStates;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LeadStates::class)
        ->assertOk();
});

/**
 * The blade binds the input to `leadStateForm.name`, so an error filed under the
 * bare `name` reaches no field and the visitor is told nothing beyond a toast.
 */
test('a validation error lands on the field the form bound it to', function (): void {
    Livewire::test(LeadStates::class)
        ->set('leadStateForm.name', null)
        ->call('save')
        ->assertHasErrors('leadStateForm.name');
});
