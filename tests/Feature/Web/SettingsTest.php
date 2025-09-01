<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings no user', function (): void {
    $this->get('/settings/clients')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings without permission', function (): void {
    Permission::findOrCreate('settings.clients.get', 'web');

    $this->actingAs($this->user, guard: 'web')->get('/settings/clients')
        ->assertStatus(403);
});
