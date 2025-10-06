<?php

use FluxErp\Livewire\Settings\Permissions;
use FluxErp\Models\User;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Permissions::class)
        ->assertOk();
});

test('show only active users', function (): void {
    User::factory(2)->create(['is_active' => true]);
    User::factory(1)->create(['is_active' => false]);

    Livewire::test(Permissions::class)
        ->assertOk()
        ->assertCount('users', 2);
});
