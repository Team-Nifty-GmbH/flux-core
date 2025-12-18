<?php

use FluxErp\Models\Permission;

test('settings translations no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/translations')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings translations page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.translations.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/translations')
        ->assertOk();
});

test('settings translations without permission', function (): void {
    Permission::findOrCreate('settings.translations.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/translations')
        ->assertForbidden();
});
