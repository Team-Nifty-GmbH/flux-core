<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

test('login link authenticates user from cached id instead of serialized model', function (): void {
    $token = 'test-token-' . uniqid();

    Cache::put('login_token_' . $token, [
        'user_type' => $this->user->getMorphClass(),
        'user_id' => $this->user->getKey(),
        'guard' => 'web',
        'intended_url' => route('dashboard'),
    ], now()->addMinutes(15));

    $url = URL::temporarySignedRoute(
        'login-link',
        now()->addMinutes(15),
        ['token' => $token]
    );

    auth()->logout();

    $response = $this->get($url);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($this->user);
});

test('login link fails gracefully with invalid token', function (): void {
    $url = URL::temporarySignedRoute(
        'login-link',
        now()->addMinutes(15),
        ['token' => 'nonexistent']
    );

    $response = $this->get($url);

    $response->assertOk();
    $response->assertViewIs('flux::login-link-failed');
});
