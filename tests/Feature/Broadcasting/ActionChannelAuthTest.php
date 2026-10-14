<?php

use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Models\Permission;
use FluxErp\Tests\Fixtures\Actions\CreateOrder as CustomizedCreateOrder;
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

    $this->authorizeChannel = function (string $channel) {
        $request = Request::create('/broadcasting/auth', 'POST', ['channel_names' => [$channel]]);
        $request->setLaravelSession(new Store('test', new ArraySessionHandler(120)));
        $request->setUserResolver(fn () => $this->user);

        return Broadcast::connection('mercure')->auth($request);
    };
});

test('a user holding the action permission may subscribe to the broadcast channel of the action', function (): void {
    $this->user->givePermissionTo(Permission::findOrCreate('action.' . CreateOrder::name(), 'web'));
    $channel = 'private-action.' . class_to_broadcast_channel(CreateOrder::class, false);

    $response = ($this->authorizeChannel)($channel);

    expect($channel)->toContain('.actions.order.')
        ->and($response->getData(true)['channel_names'])->toBe([['name' => $channel]]);
});

test('a user without the action permission may not subscribe to the broadcast channel of the action', function (): void {
    Permission::findOrCreate('action.' . CreateOrder::name(), 'web');
    $channel = 'private-action.' . class_to_broadcast_channel(CreateOrder::class, false);

    $response = ($this->authorizeChannel)($channel);

    expect($response->getData(true)['channel_names'])->toBe([['name' => $channel, 'denied' => true]]);
});

test('a channel of an unknown action is denied', function (): void {
    $channel = 'private-action.fluxerp.actions.order.doesnotexist';

    $response = ($this->authorizeChannel)($channel);

    expect($response->getData(true)['channel_names'])->toBe([['name' => $channel, 'denied' => true]]);
});

test('the channel of a bound action class is authorized by the permission of the action', function (): void {
    app()->bind(CreateOrder::class, CustomizedCreateOrder::class);
    Permission::findOrCreate('action.' . CreateOrder::name(), 'web');
    $channel = 'private-action.' . class_to_broadcast_channel(CustomizedCreateOrder::class, false);

    $denied = ($this->authorizeChannel)($channel)->getData(true)['channel_names'];
    $this->user->givePermissionTo('action.' . CreateOrder::name());
    $allowed = ($this->authorizeChannel)($channel)->getData(true)['channel_names'];

    expect($denied)->toBe([['name' => $channel, 'denied' => true]])
        ->and($allowed)->toBe([['name' => $channel]]);
});
