<?php

use FluxErp\Models\Language;
use FluxErp\Models\User;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Contracts\Broadcasting\Broadcaster as BroadcasterContract;

function registeredBroadcastChannels(): array
{
    $broadcaster = app(BroadcasterContract::class);
    $property = (new ReflectionClass(Broadcaster::class))->getProperty('channels');
    $property->setAccessible(true);

    return $property->getValue($broadcaster);
}

function broadcastingUser(): User
{
    return User::factory()->create(['language_id' => Language::factory()->create()->id]);
}

test('the user channel authorizes only its owner', function (): void {
    $user = broadcastingUser();
    $other = broadcastingUser();

    $authorize = registeredBroadcastChannels()['user.{id}'];

    expect($authorize($user, $user->getKey()))->toBeTrue()
        ->and($authorize($user, $other->getKey()))->toBeFalse();
});

test('the user model has no generic existence channel', function (): void {
    $channels = array_keys(registeredBroadcastChannels());

    expect($channels)->not->toContain('user.{user}')
        ->and($channels)->not->toContain('user.');
});

test('the broadcasting connection endpoint exposes only public values', function (): void {
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb' => [
            'key' => 'app-key',
            'secret' => 'app-secret',
            'options' => ['host' => 'ws.example.test', 'port' => 443, 'scheme' => 'https'],
        ],
    ]);

    $this->getJson('/api/broadcasting/connection')
        ->assertOk()
        ->assertExactJson([
            'key' => 'app-key',
            'host' => 'ws.example.test',
            'port' => 443,
            'scheme' => 'https',
        ]);
});
