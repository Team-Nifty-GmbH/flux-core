<?php

uses(FluxErp\Tests\Feature\Web\BaseSetup::class);
use FluxErp\Models\Permission;

test('settings clients customer portal client not found', function (): void {
    $this->dbClient->delete();

    $this->user->givePermissionTo(
        Permission::findOrCreate('settings.clients.{client}.customer-portal.get', 'web')
    );

    $this->actingAs($this->user, 'web')->get(
        '/settings/clients/' . $this->dbClient->getKey() . '/customer-portal'
    )
        ->assertStatus(404);
});

test('settings clients customer portal no user', function (): void {
    $this->get('/settings/clients/' . $this->dbClient->getKey() . '/customer-portal')
        ->assertStatus(302)
        ->assertRedirect(route('login'));
});

test('settings clients customer portal page', function (): void {
    $this->user->givePermissionTo(
        Permission::findOrCreate('settings.clients.{client}.customer-portal.get', 'web')
    );

    $this->actingAs($this->user, 'web')->get(
        '/settings/clients/' . $this->dbClient->getKey() . '/customer-portal'
    )
        ->assertStatus(200);
});

test('settings clients customer portal without permission', function (): void {
    Permission::findOrCreate('settings.clients.{client}.customer-portal.get', 'web');

    $this->actingAs($this->user, 'web')->get(
        '/settings/clients/' . $this->dbClient->getKey() . '/customer-portal'
    )
        ->assertStatus(403);
});
