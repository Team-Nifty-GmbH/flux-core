<?php

use FluxErp\Events\BroadcastableModelEventOccurred;
use FluxErp\Models\Address;
use FluxErp\Models\PrintJob;
use FluxErp\Models\Token;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;

beforeEach(function (): void {
    config([
        'broadcasting.default' => 'mercure',
        'broadcasting.connections.mercure' => [
            'driver' => 'mercure',
            'url' => 'http://localhost/.well-known/mercure',
            'public_url' => 'http://localhost/.well-known/mercure',
            'secret' => str_repeat('s', 32),
            'cookie_name' => 'mercureAuthorization',
        ],
        'auth.guards.address' => ['driver' => 'session', 'provider' => 'addresses'],
        'auth.providers.addresses' => ['driver' => 'eloquent', 'model' => Address::class],
    ]);
    Broadcast::purge('mercure');
    require flux_path('routes/channels.php');
    Event::fake([BroadcastableModelEventOccurred::class]);
});

function authorizeWithApiToken(object $test, string $channel, array $abilities = ['*'], ?int $id = null): TestResponse
{
    $token = Token::query()->forceCreate(array_filter(['id' => $id, 'name' => 'printer-bridge']));
    Auth::forgetGuards();

    return $test->withToken($token->createToken('printer-bridge', $abilities)->plainTextToken)
        ->postJson('/api/broadcasting/auth', ['channel_names' => [$channel]]);
}

test('an api token authorizes the channel of a model at the api broadcasting route', function (): void {
    $channel = 'private-' . class_to_broadcast_channel(PrintJob::class, false) . '.';

    authorizeWithApiToken($this, $channel)
        ->assertOk()
        ->assertJsonPath('channel_names.0.name', $channel)
        ->assertJsonMissingPath('channel_names.0.denied')
        ->assertCookie('mercureAuthorization');
});

test('an api token without the user or interface ability is refused', function (): void {
    authorizeWithApiToken($this, 'private-' . class_to_broadcast_channel(PrintJob::class, false) . '.', ['address'])
        ->assertForbidden();
});

test('an api token may not join channels meant for users', function (string $channel): void {
    authorizeWithApiToken($this, $channel)
        ->assertOk()
        ->assertJsonPath('channel_names.0.denied', true);
})->with([
    'job batch' => ['private-job-batch.1'],
    'presence' => ['presence-presence'],
]);

test('an api token may not join the channel of the user sharing its id', function (): void {
    $id = $this->user->getKey();

    authorizeWithApiToken(
        $this,
        'private-' . class_to_broadcast_channel(morphed_model('user'), false) . '.' . $id,
        id: $id
    )
        ->assertOk()
        ->assertJsonPath('channel_names.0.denied', true);
});
