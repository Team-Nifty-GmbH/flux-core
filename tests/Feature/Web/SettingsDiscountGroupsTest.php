<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings discount groups no user', function (): void {
    $this->get('/settings/discount-groups')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings discount groups page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.discount-groups.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/discount-groups')
        ->assertStatus(200);
});

test('settings discount groups without permission', function (): void {
    Permission::findOrCreate('settings.discount-groups.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/discount-groups')
        ->assertStatus(403);
});
