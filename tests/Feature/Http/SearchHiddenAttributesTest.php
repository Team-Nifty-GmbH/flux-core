<?php

use FluxErp\Models\Contact;
use FluxErp\Models\User;
use Illuminate\Testing\TestResponse;

beforeEach(function (): void {
    $this->target = User::factory()->create(['password' => 'secret-password']);
    $this->target->forceFill(['remember_token' => 'secret-remember-token'])->saveQuietly();
    $this->hash = $this->target->getRawOriginal('password');
});

function searchPayload(TestResponse $response): string
{
    return json_encode($response->json(), JSON_UNESCAPED_SLASHES);
}

test('requested fields never include hidden attributes', function (): void {
    $response = $this->post(route('search', User::class), [
        'selected' => [$this->target->getKey()],
        'fields' => ['email', 'password', 'remember_token'],
    ]);

    $response->assertOk();

    expect($response->json('0'))
        ->toHaveKey('email', $this->target->email)
        ->not->toHaveKey('password')
        ->not->toHaveKey('remember_token');
});

test('requested appends never include hidden attributes', function (): void {
    $response = $this->post(route('search', User::class), [
        'selected' => [$this->target->getKey()],
        'appends' => ['password', 'remember_token'],
    ]);

    $response->assertOk();

    expect($response->json('0'))
        ->not->toHaveKey('password')
        ->not->toHaveKey('remember_token');
});

test('a selected hidden column is not returned', function (): void {
    $response = $this->post(route('search', User::class), [
        'search' => '',
        'searchFields' => ['email'],
        'where' => [['id', '=', $this->target->getKey()]],
        'select' => ['id', 'email', 'firstname', 'lastname', 'name', 'password', 'remember_token'],
        'fields' => ['email', 'password', 'remember_token'],
        'mapping' => ['leak' => 'password'],
    ]);

    $response->assertOk();

    expect($response->json('0'))
        ->toHaveKey('email', $this->target->email)
        ->not->toHaveKey('password')
        ->not->toHaveKey('remember_token')
        ->and($response->json('0.leak'))->toBeNull()
        ->and(searchPayload($response))
        ->not->toContain('secret-remember-token')
        ->not->toContain($this->hash);
});

test('a hidden column cannot be renamed past the hidden list', function (): void {
    $response = $this->post(route('search', User::class), [
        'search' => '',
        'searchFields' => ['email'],
        'where' => [['id', '=', $this->target->getKey()]],
        'select' => ['id', 'email', 'firstname', 'lastname', 'name', 'password as pw', 'PASSWORD', 'users.password as pw2'],
        'fields' => ['email', 'pw', 'PASSWORD', 'pw2'],
    ]);

    $response->assertOk();

    expect($response->json('0'))->toHaveKey('email', $this->target->email)
        ->and(searchPayload($response))->not->toContain($this->hash);
});

test('a hidden column of an eager loaded relation cannot be renamed past the hidden list', function (): void {
    Contact::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create(['agent_id' => $this->target->getKey()]);

    $response = $this->post(route('search', Contact::class), [
        'search' => '',
        'searchFields' => ['customer_number'],
        'with' => ['agent:id,password as pw,PASSWORD'],
        'fields' => ['agent'],
    ]);

    $response->assertOk();

    expect($response->json('0.agent.id'))->toBe($this->target->getKey())
        ->and(searchPayload($response))->not->toContain($this->hash);
});
