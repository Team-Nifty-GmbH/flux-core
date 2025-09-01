<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('calendars no user', function (): void {
    $this->get('/calendars')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('calendars page', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('calendars.get', 'web'));

    $this->actingAs($this->user, 'web')->get('/calendars')
        ->assertStatus(200);
});

test('calendars without permission', function (): void {
    Permission::findOrCreate('calendars.get', 'web');

    $this->actingAs($this->user, 'web')->get('/calendars')
        ->assertStatus(403);
});
