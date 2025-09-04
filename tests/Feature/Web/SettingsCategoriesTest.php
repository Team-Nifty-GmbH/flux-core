<?php

use FluxErp\Models\Permission;

test('settings categories no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/categories')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings categories page', function (): void {
    $this->user->givePermissionTo(
        Permission::findOrCreate('settings.categories.get', 'web')
    );

    $this->actingAs($this->user, 'web')->get('/settings/categories')
        ->assertOk();
});

test('settings categories without permission', function (): void {
    Permission::findOrCreate('settings.categories.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/categories')
        ->assertForbidden();
});
