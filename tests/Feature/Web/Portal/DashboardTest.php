<?php

uses(FluxErp\Tests\Feature\Web\Portal\PortalSetup::class);
test('portal dashboard no user', function (): void {
    $this->get(route('portal.dashboard'))
        ->assertStatus(302)
        ->assertRedirect($this->portalDomain . '/login');
});

test('portal dashboard page', function (): void {
    $this->actingAs($this->user, 'address')->get(route('portal.dashboard'))
        ->assertStatus(200);
});
