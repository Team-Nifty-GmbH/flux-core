<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings languages no user', function (): void {
    $this->get('/settings/languages')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings languages page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('settings.languages.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/settings/languages')
        ->assertStatus(200);
});

test('settings languages without permission', function (): void {
    Permission::findOrCreate('settings.languages.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/languages')
        ->assertStatus(403);
});
