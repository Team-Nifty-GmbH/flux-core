<?php

uses(FluxErp\Tests\Feature\Web\Portal\PortalSetup::class);
test('login as authenticated user', function (): void {
    $this->actingAs($this->user, 'address')->get($this->portalDomain . '/login')
        ->assertStatus(302)
        ->assertRedirect(route('portal.dashboard'));
});

test('login no path', function (): void {
    $this->get($this->portalDomain . '/')
        ->assertStatus(302)
        ->assertRedirect($this->portalDomain . '/login');
});

test('login page', function (): void {
    $this->get($this->portalDomain . '/login')
        ->assertStatus(200);
});
