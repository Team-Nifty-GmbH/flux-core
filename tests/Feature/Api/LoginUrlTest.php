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
