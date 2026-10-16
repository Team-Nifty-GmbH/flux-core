<?php

use FluxErp\Actions\ActionManager;
use FluxErp\Models\Permission;
use FluxErp\Models\Token;
use FluxErp\Tests\Fixtures\Actions\CreateBroadcastFixture;
use FluxErp\Tests\Fixtures\Actions\Customized\CreateBroadcastFixture as CustomizedCreateBroadcastFixture;
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
    Broadcast::purge('mercure');
    require flux_path('routes/channels.php');

    app(ActionManager::class)->register(CreateBroadcastFixture::name(), CreateBroadcastFixture::class);
    $this->permission = Permission::findOrCreate('action.' . CreateBroadcastFixture::name(), 'web');
    $this->channel = 'private-' . CreateBroadcastFixture::getBroadcastChannel();
});

function authorizeActionChannel(object $test, string $channel): array
{
    $request = Request::create('/broadcasting/auth', 'POST', ['channel_names' => [$channel]]);
    $request->setLaravelSession(new Store('test', new ArraySessionHandler(120)));
    $request->setUserResolver(fn () => $test->user);

    /** @var JsonResponse $response */
    $response = Broadcast::connection('mercure')->auth($request);

    return $response->getData(true)['channel_names'];
}

test('the action channel is a single segment after the action prefix', function (): void {
    expect(CreateBroadcastFixture::getBroadcastChannel())->toBe('action.broadcast_fixture-create');
});

test('a user allowed to run the action may subscribe to its channel', function (): void {
    $this->user->givePermissionTo($this->permission);

    expect(authorizeActionChannel($this, $this->channel))->toBe([['name' => $this->channel]]);
});

test('a user not allowed to run the action may not subscribe to its channel', function (): void {
    expect(authorizeActionChannel($this, $this->channel))
        ->toBe([['name' => $this->channel, 'denied' => true]]);
});

test('the channel of an unknown action is denied', function (): void {
    $this->user->givePermissionTo($this->permission);

    expect(authorizeActionChannel($this, 'private-action.unknown-create'))
        ->toBe([['name' => 'private-action.unknown-create', 'denied' => true]]);
});

test('an api token may not subscribe to an action channel', function (): void {
    $token = Token::query()->forceCreate(['name' => 'printer-bridge']);
    $token->givePermissionTo(Permission::findOrCreate('action.' . CreateBroadcastFixture::name(), 'token'));
    Auth::forgetGuards();

    $this->withToken($token->createToken('printer-bridge', ['*'])->plainTextToken)
        ->postJson('/api/broadcasting/auth', ['channel_names' => [$this->channel]])
        ->assertOk()
        ->assertJsonPath('channel_names.0.denied', true);
});

test('a bound action class decides who may subscribe to the action channel', function (): void {
    app()->bind(CreateBroadcastFixture::class, CustomizedCreateBroadcastFixture::class);
    $channel = 'private-' . resolve_static(CreateBroadcastFixture::class, 'getBroadcastChannel');

    expect($channel)->toBe($this->channel)
        ->and(authorizeActionChannel($this, $channel))->toBe([['name' => $channel]]);
});

test('the channel of an action whose permission does not exist is denied', function (): void {
    $this->permission->delete();

    expect(authorizeActionChannel($this, $this->channel))
        ->toBe([['name' => $this->channel, 'denied' => true]]);
});

test('a user bearer token may subscribe to an action channel only with the action permission', function (): void {
    Auth::forgetGuards();
    $plainTextToken = $this->user->createToken('mobile', ['user'])->plainTextToken;

    $this->withToken($plainTextToken)
        ->postJson('/api/broadcasting/auth', ['channel_names' => [$this->channel]])
        ->assertOk()
        ->assertJsonPath('channel_names.0.denied', true);

    $this->user->givePermissionTo($this->permission);
    Auth::forgetGuards();

    $this->withToken($plainTextToken)
        ->postJson('/api/broadcasting/auth', ['channel_names' => [$this->channel]])
        ->assertOk()
        ->assertJsonMissingPath('channel_names.0.denied');
});

test('a user bearer token may not subscribe to the channel of an action whose permission does not exist', function (): void {
    $this->permission->delete();
    Auth::forgetGuards();

    $this->withToken($this->user->createToken('mobile', ['user'])->plainTextToken)
        ->postJson('/api/broadcasting/auth', ['channel_names' => [$this->channel]])
        ->assertOk()
        ->assertJsonPath('channel_names.0.denied', true);
});
