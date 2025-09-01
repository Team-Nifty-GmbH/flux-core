<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings currencies no user', function (): void {
    $this->get('/settings/currencies')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings currencies page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.currencies.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/currencies')
        ->assertStatus(200);
});

test('settings currencies without permission', function (): void {
    Permission::findOrCreate('settings.currencies.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/currencies')
        ->assertStatus(403);
});
