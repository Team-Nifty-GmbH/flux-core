<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings countries no user', function (): void {
    $this->get('/settings/countries')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings countries page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.countries.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/countries')
        ->assertStatus(200);
});

test('settings countries without permission', function (): void {
    Permission::findOrCreate('settings.countries.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/countries')
        ->assertStatus(403);
});
