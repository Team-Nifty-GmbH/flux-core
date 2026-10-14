<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
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

    Auth::forgetGuards();
});

function authorizeChannels(array $channels): JsonResponse
{
    Broadcast::purge('mercure');
    require __DIR__ . '/../../../routes/channels.php';

    $request = Request::create('/broadcasting/auth', 'POST', ['channel_names' => $channels]);
    $request->setLaravelSession(new Store('test', new ArraySessionHandler(120)));
    $request->setUserResolver(fn (?string $guard = null) => auth()->guard($guard)->user());

    return Broadcast::connection('mercure')->auth($request);
}

test('a guest is denied model and user channels when no address guard is configured', function (): void {
    expect(config('auth.guards'))->not->toHaveKey('address');

    $response = authorizeChannels(['private-order.1', 'private-user.1']);

    expect($response->getData(true)['channel_names'])->toBe([
        ['name' => 'private-order.1', 'denied' => true],
        ['name' => 'private-user.1', 'denied' => true],
    ]);
});

test('a logged in address can still authorize its channels when the address guard is configured', function (): void {
    config([
        'auth.guards.address' => ['driver' => 'session', 'provider' => 'addresses'],
        'auth.providers.addresses' => ['driver' => 'eloquent', 'model' => Address::class],
    ]);

    $address = Address::factory()->create([
        'contact_id' => Contact::factory()->create()->getKey(),
    ]);
    $this->be($address, 'address');

    $response = authorizeChannels(['private-address.' . $address->getKey()]);

    expect($response->getData(true)['channel_names'])->toBe([
        ['name' => 'private-address.' . $address->getKey()],
    ]);
});
