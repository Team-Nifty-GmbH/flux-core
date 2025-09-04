<?php

test('login as authenticated user', function (): void {
    $this->actingAs($this->address, 'address')->get(config('flux.portal_domain') . '/login')
        ->assertFound()
        ->assertRedirect(route('portal.dashboard'));
});

test('login no path', function (): void {
    $this->actingAsGuest();
    $this->get(config('flux.portal_domain') . '/')
        ->assertFound()
        ->assertRedirect(config('flux.portal_domain') . '/login');
});

test('login page', function (): void {
    $this->actingAsGuest();
    $this->get(config('flux.portal_domain') . '/login')
        ->assertOk();
});
