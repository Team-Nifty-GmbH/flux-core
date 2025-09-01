<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings order types no user', function (): void {
    $this->get('/settings/order-types')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings order types page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.order-types.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/order-types')
        ->assertStatus(200);
});

test('settings order types without permission', function (): void {
    Permission::findOrCreate('settings.order-types.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/order-types')
        ->assertStatus(403);
});
