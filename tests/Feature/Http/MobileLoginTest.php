<?php

test('mobile login with an empty redirect falls back to the dashboard', function (): void {
    auth()->logout();

    $response = $this->get(route('mobile.login') . '?redirect=');

    $response->assertRedirect(route('dashboard', absolute: false));
    $response->assertSessionMissing('url.intended');
});

test('mobile login stores the redirect as intended url for guests', function (): void {
    auth()->logout();

    $response = $this->get(route('mobile.login', ['redirect' => '/orders']));

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('url.intended', url('/orders'));
});
