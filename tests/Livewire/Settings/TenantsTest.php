<?php

use FluxErp\Livewire\Settings\Tenants;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Tenants::class)
        ->assertOk();
});

test('open new modal', function (): void {
    Livewire::test(Tenants::class)
        ->call('edit', null)
        ->assertOk()
        ->assertHasNoErrors();
});
