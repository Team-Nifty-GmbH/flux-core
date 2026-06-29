<?php

use FluxErp\Livewire\Settings\Scheduling;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Scheduling::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(Scheduling::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('schedule.id', null)
        ->assertSet('schedule.name', null)
        ->assertSet('schedule.is_active', true);
});
