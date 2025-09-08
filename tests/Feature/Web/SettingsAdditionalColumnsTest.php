<?php

use FluxErp\Models\Permission;

test('settings additional columns no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/additional-columns')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('settings additional columns page', function (): void {
    $this->user->givePermissionTo(
        Permission::findOrCreate('settings.additional-columns.get', 'web')
    );

    $this->actingAs($this->user, 'web')->get('/settings/additional-columns')
        ->assertOk();
});

test('settings additional columns without permission', function (): void {
    Permission::findOrCreate('settings.additional-columns.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/additional-columns')
        ->assertForbidden();
});
