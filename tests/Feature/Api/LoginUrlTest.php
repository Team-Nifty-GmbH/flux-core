<?php

use FluxErp\Models\Language;
use FluxErp\Models\User;
use Laravel\Sanctum\Sanctum;

test('the login url endpoint returns a signed magic login url', function (): void {
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->postJson('/api/user/login-url');

    $response->assertOk();

    expect($response->json('data.url'))
        ->toContain('login-link')
        ->toContain('signature=');
});

test('the login url endpoint requires the user ability', function (): void {
    $user = User::factory()->create(['language_id' => Language::factory()->create()->id]);
    Sanctum::actingAs($user, ['interface']);

    $this->postJson('/api/user/login-url')->assertForbidden();
});

test('the login url redirects to a named route', function (): void {
    Sanctum::actingAs($this->user, ['user']);

    $url = $this->postJson('/api/user/login-url', ['redirect' => 'dashboard'])
        ->assertOk()
        ->json('data.url');

    $this->get($url)->assertRedirect(route('dashboard'));
});

test('the login url resolves a named route with parameters', function (): void {
    Sanctum::actingAs($this->user, ['user']);

    $url = $this->postJson('/api/user/login-url', [
        'redirect' => 'orders.id',
        'redirect_params' => ['id' => 5],
    ])
        ->assertOk()
        ->json('data.url');

    $this->get($url)->assertRedirect(route('orders.id', ['id' => 5]));
});

test('the login url rejects an unknown route name', function (): void {
    Sanctum::actingAs($this->user, ['user']);

    $this->postJson('/api/user/login-url', ['redirect' => 'this.route.does.not.exist'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('redirect');
});

test('the login url rejects a named route with missing parameters', function (): void {
    Sanctum::actingAs($this->user, ['user']);

    $this->postJson('/api/user/login-url', ['redirect' => 'orders.id'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('redirect');
});
