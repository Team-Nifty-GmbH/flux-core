<?php

test('portal dashboard no user', function (): void {
    $this->actingAsGuest();

    $this->get(route('portal.dashboard'))
        ->assertFound()
        ->assertRedirect(config('flux.portal_domain') . '/login');
});

test('portal dashboard page', function (): void {
    $this->actingAs($this->address, 'address')->get(route('portal.dashboard'))
        ->assertOk();
});
