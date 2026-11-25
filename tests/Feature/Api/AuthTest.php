<?php

use FluxErp\Models\Permission;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

test('authenticate', function (): void {
    $response = $this->post('/api/auth/token', [
        'email' => $this->user->email,
        'password' => 'password',
    ]);
    $response->assertOk();

    $token = json_decode($response->getContent())->access_token;
    expect($token)->not->toBeEmpty();
});

test('authenticate invalid credentials', function (): void {
    $response = $this->post('/api/auth/token', [
        'email' => $this->user->email,
        'password' => Str::random(),
    ]);

    $response->assertUnauthorized();
});

test('authenticate validation fails', function (): void {
    $response = $this->post('/api/auth/token', [
        'email' => 42,
        'password' => 42,
    ]);

    $response->assertUnprocessable();
});

test('validate token', function (): void {
    $permission = Permission::findOrCreate('api.auth.token.validate.get');
    $this->user->givePermissionTo($permission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/auth/token/validate');
    $response->assertOk();
});

test('authenticate is locked after too many failed attempts', function (): void {
    foreach (range(1, 5) as $attempt) {
        $this->post('/api/auth/token', ['email' => $this->user->email, 'password' => Str::random()])
            ->assertUnauthorized();
    }

    $this->post('/api/auth/token', ['email' => $this->user->email, 'password' => 'password'])
        ->assertTooManyRequests()
        ->assertJsonMissingPath('access_token');
});
