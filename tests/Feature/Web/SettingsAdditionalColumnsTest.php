<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings additional columns no user', function (): void {
    $this->get('/settings/additional-columns')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings additional columns page', function (): void {
    $this->user->givePermissionTo(
        Permission::findOrCreate('settings.additional-columns.get', 'web')
    );

    $this->actingAs($this->user, 'web')->get('/settings/additional-columns')
        ->assertStatus(200);
});

test('settings additional columns without permission', function (): void {
    Permission::findOrCreate('settings.additional-columns.get', 'web');

    $this->actingAs($this->user, 'web')->get('/settings/additional-columns')
        ->assertStatus(403);
});
