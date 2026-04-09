<?php

use FluxErp\Livewire\Settings\Permissions;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Permissions::class)
        ->assertOk();
});

test('open new modal', function (): void {
    Livewire::test(Permissions::class)
        ->call('edit', null)
        ->assertOk()
        ->assertHasNoErrors();
});
