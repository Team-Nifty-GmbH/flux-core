<?php

use FluxErp\Livewire\Settings\Permissions;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Permissions::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(Permissions::class)
        ->call('edit', null)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('roleForm.id', null)
        ->assertSet('roleForm.name', null)
        ->assertSet('roleForm.guard_name', null)
        ->assertOpensModal('edit-role-permissions-modal');
});
