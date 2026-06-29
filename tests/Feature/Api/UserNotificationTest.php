<?php

use FluxErp\Models\Permission;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

function makeNotification($user, array $data = [], $readAt = null)
{
    return $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\TestNotification',
        'data' => array_merge([
            'title' => 'A title',
            'description' => 'A description',
            'toastType' => 'info',
            'accept' => ['url' => 'https://example.test/tickets/1'],
        ], $data),
        'read_at' => $readAt,
    ]);
}

beforeEach(function (): void {
    $this->indexPermission = Permission::findOrCreate('api.user.notifications.get', 'sanctum');
    $this->readPermission = Permission::findOrCreate('api.user.notifications.read.post', 'sanctum');
});

test('the notifications endpoint returns the user notifications with an unread count', function (): void {
    makeNotification($this->user);
    $this->user->givePermissionTo($this->indexPermission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->getJson('/api/user/notifications')->assertOk();

    expect($response->json('data.data'))->toHaveCount(1);
    expect($response->json('data.data.0'))->toHaveKeys(['id', 'title', 'description', 'type', 'url', 'read_at', 'created_at']);
    expect($response->json('data.data.0.title'))->toBe('A title');
    expect($response->json('data.data.0.url'))->toBe('https://example.test/tickets/1');
    expect($response->json('unread_count'))->toBe(1);
});

test('marking a single notification read drops the unread count', function (): void {
    $n = makeNotification($this->user);
    $this->user->givePermissionTo($this->readPermission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->postJson('/api/user/notifications/read', ['id' => $n->getKey()])->assertOk();

    expect($response->json('data.unread_count'))->toBe(0);
    expect($n->fresh()->read_at)->not->toBeNull();
});

test('marking all notifications read clears the unread count', function (): void {
    makeNotification($this->user);
    makeNotification($this->user);
    $this->user->givePermissionTo($this->readPermission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->postJson('/api/user/notifications/read', ['all' => true])->assertOk();

    expect($response->json('data.unread_count'))->toBe(0);
    expect($this->user->unreadNotifications()->count())->toBe(0);
});

test('marking read rejects an id that does not belong to the user', function (): void {
    $other = FluxErp\Models\User::factory()->create(['language_id' => $this->user->language_id]);
    $foreign = makeNotification($other);
    $this->user->givePermissionTo($this->readPermission);
    Sanctum::actingAs($this->user, ['user']);

    $this->postJson('/api/user/notifications/read', ['id' => $foreign->getKey()])
        ->assertStatus(422)
        ->assertJsonValidationErrorFor('id');
});

test('marking read requires either an id or all', function (): void {
    $this->user->givePermissionTo($this->readPermission);
    Sanctum::actingAs($this->user, ['user']);

    $this->postJson('/api/user/notifications/read', [])
        ->assertStatus(422);
});
