<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings permissions no user', function (): void {
    $this->get('/settings/permissions')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings permissions page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.permissions.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/permissions')
        ->assertStatus(200);
});

test('settings permissions without permission', function (): void {
    Permission::findOrCreate('settings.permissions.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/permissions')
        ->assertStatus(403);
});
