<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings categories no user', function (): void {
    $this->get('/settings/categories')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings categories page', function (): void {
    $this->user->givePermissionTo(
        Permission::findOrCreate('settings.categories.get', 'web')
    );

    $this->actingAs($this->user, 'web')->get('/settings/categories')
        ->assertStatus(200);
});

test('settings categories without permission', function (): void {
    Permission::findOrCreate('settings.categories.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/categories')
        ->assertStatus(403);
});
