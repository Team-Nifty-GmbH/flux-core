<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings price lists no user', function (): void {
    $this->get('/settings/price-lists')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings price lists page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.price-lists.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/price-lists')
        ->assertStatus(200);
});

test('settings price lists without permission', function (): void {
    Permission::findOrCreate('settings.price-lists.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/price-lists')
        ->assertStatus(403);
});
