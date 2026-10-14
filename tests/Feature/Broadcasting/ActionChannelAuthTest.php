<?php

use FluxErp\Actions\Order\CreateOrder;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
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
    require __DIR__ . '/../../../routes/channels.php';
});

test('an authenticated user may subscribe to the broadcast channel of an action', function (): void {
    $channel = 'private-action.' . class_to_broadcast_channel(CreateOrder::class, false);

    $request = Request::create('/broadcasting/auth', 'POST', ['channel_names' => [$channel]]);
    $request->setLaravelSession(new Store('test', new ArraySessionHandler(120)));
    $request->setUserResolver(fn () => $this->user);

    $response = Broadcast::connection('mercure')->auth($request);

    expect($channel)->toContain('.actions.order.')
        ->and($response->getData(true)['channel_names'])->toBe([['name' => $channel]]);
});
