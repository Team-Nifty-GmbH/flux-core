<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings logs no user', function (): void {
    $this->get('/settings/logs')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings logs page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.logs.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/logs')
        ->assertStatus(200);
});

test('settings logs without permission', function (): void {
    Permission::findOrCreate('settings.logs.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/logs')
        ->assertStatus(403);
});
