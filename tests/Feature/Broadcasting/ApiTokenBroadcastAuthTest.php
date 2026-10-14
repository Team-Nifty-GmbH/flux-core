<?php

use FluxErp\Models\PrintJob;
use FluxErp\Models\Token;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

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
    ]);
    Broadcast::purge('mercure');
    require flux_path('routes/channels.php');
});

test('an api token authorizes the channel of a model at the api broadcasting route', function (): void {
    $token = Token::query()->create(['name' => 'printer-bridge']);
    $channel = 'private-' . class_to_broadcast_channel(PrintJob::class, false) . '.';
    Auth::forgetGuards();

    $this->withToken($token->createToken('printer-bridge', ['*'])->plainTextToken)
        ->postJson('/api/broadcasting/auth', ['channel_names' => [$channel]])
        ->assertOk()
        ->assertJsonPath('channel_names.0.name', $channel)
        ->assertJsonMissingPath('channel_names.0.denied')
        ->assertCookie('mercureAuthorization');
});
