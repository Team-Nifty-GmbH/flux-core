<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings translations no user', function (): void {
    $this->get('/settings/translations')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings translations page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.translations.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/translations')
        ->assertStatus(200);
});

test('settings translations without permission', function (): void {
    Permission::findOrCreate('settings.translations.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/translations')
        ->assertStatus(403);
});
