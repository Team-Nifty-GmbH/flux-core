<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $language = Language::factory()->create();

    $this->password = 'password';

    $this->user = new User();
    $this->user->language_id = $language->id;
    $this->user->email = 'TestUser';
    $this->user->firstname = 'TestUserFirstname';
    $this->user->lastname = 'TestUserLastname';
    $this->user->password = $this->password;
    $this->user->is_active = true;
    $this->user->save();
});

test('authenticate', function (): void {
    $response = $this->post('/api/auth/token', [
        'email' => $this->user->email,
        'password' => $this->password,
    ]);
    $response->assertStatus(200);

    $token = json_decode($response->getContent())->access_token;
    expect($token)->not->toBeEmpty();
});

test('authenticate invalid credentials', function (): void {
    $response = $this->post('/api/auth/token', [
        'email' => $this->user->email,
        'password' => Str::random(),
    ]);

    $response->assertStatus(401);
});

test('authenticate validation fails', function (): void {
    $response = $this->post('/api/auth/token', [
        'email' => 42,
        'password' => 42,
    ]);

    $response->assertStatus(422);
});

test('validate token', function (): void {
    $permission = Permission::findOrCreate('api.auth.token.validate.get');
    $this->user->givePermissionTo($permission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/auth/token/validate');
    $response->assertStatus(200);
});
