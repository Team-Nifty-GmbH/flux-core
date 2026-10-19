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

test('mobile login does not store an external redirect as intended url', function (string $redirect): void {
    auth()->logout();

    $response = $this->get(route('mobile.login', ['redirect' => $redirect]));

    $response->assertRedirect(route('dashboard', absolute: false));
    $response->assertSessionMissing('url.intended');
})->with([
    'https://evil.example/orders',
    '//evil.example/orders',
]);

test('mobile login does not redirect an authenticated user to an external url', function (string $redirect): void {
    $response = $this->get(route('mobile.login', ['redirect' => $redirect]));

    $response->assertRedirect(route('dashboard', absolute: false));
})->with([
    'https://evil.example/orders',
    '//evil.example/orders',
]);

test('mobile login redirects an authenticated user to an internal path', function (): void {
    $response = $this->get(route('mobile.login', ['redirect' => '/orders']));

    $response->assertRedirect(url('/orders'));
});
