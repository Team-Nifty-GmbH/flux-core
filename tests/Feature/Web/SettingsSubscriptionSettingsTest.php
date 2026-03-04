<?php

use FluxErp\Models\Permission;

test('subscription settings no user', function (): void {
    $this->actingAsGuest();

    $this->get('/settings/subscription-settings')
        ->assertFound()
        ->assertRedirect(route('login'));
});

test('subscription settings without permission', function (): void {
    Permission::findOrCreate('settings.subscription-settings.get', 'web');

    $this->actingAs($this->user, guard: 'web')->get('/settings/subscription-settings')
        ->assertForbidden();
});
