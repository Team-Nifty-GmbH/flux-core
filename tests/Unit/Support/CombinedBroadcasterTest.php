<?php

use FluxErp\Support\Broadcasting\CombinedBroadcaster;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\Broadcasters\MercureBroadcaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Exceptions;

function recordingBroadcaster(): Broadcaster
{
    return new class() extends Broadcaster
    {
        public array $broadcasts = [];

        public array $authRequests = [];

        public function auth($request)
        {
            $this->authRequests[] = $request;

            return 'auth by ' . self::class;
        }

        public function validAuthenticationResponse($request, $result)
        {
            return $result;
        }

        public function broadcast(array $channels, $event, array $payload = []): void
        {
            $this->broadcasts[] = [$channels, $event, $payload];
        }

        public function registeredChannels(): array
        {
            return array_keys($this->channels);
        }
    };
}

function mercureBroadcaster(): MercureBroadcaster
{
    return Mockery::mock(MercureBroadcaster::class);
}

test('an event reaches every connection', function (): void {
    $first = recordingBroadcaster();
    $second = recordingBroadcaster();

    (new CombinedBroadcaster([$first, $second]))->broadcast(['private-order.1'], 'OrderUpdated', ['id' => 1]);

    expect($first->broadcasts)->toBe([[['private-order.1'], 'OrderUpdated', ['id' => 1]]])
        ->and($second->broadcasts)->toBe([[['private-order.1'], 'OrderUpdated', ['id' => 1]]]);
});

function failingBroadcaster(): Broadcaster
{
    return new class() extends Broadcaster
    {
        public function auth($request): void {}

        public function validAuthenticationResponse($request, $result): void {}

        public function broadcast(array $channels, $event, array $payload = []): void
        {
            throw new RuntimeException('hub unreachable');
        }
    };
}

test('an event still reaches the other connections when one fails', function (): void {
    Exceptions::fake();
    $second = recordingBroadcaster();

    (new CombinedBroadcaster([failingBroadcaster(), $second]))->broadcast(['private-order.1'], 'OrderUpdated');

    expect($second->broadcasts)->toBe([[['private-order.1'], 'OrderUpdated', []]]);
    Exceptions::assertReported(fn (RuntimeException $e) => $e->getMessage() === 'hub unreachable');
});

test('a broadcast fails when no connection takes the event', function (): void {
    expect(fn () => (new CombinedBroadcaster([failingBroadcaster(), failingBroadcaster()]))
        ->broadcast(['private-order.1'], 'OrderUpdated'))
        ->toThrow(RuntimeException::class, 'hub unreachable');
});

test('a channel is registered on every connection', function (): void {
    $first = recordingBroadcaster();
    $second = recordingBroadcaster();

    (new CombinedBroadcaster([$first, $second]))->channel('order.{id}', fn () => true);

    expect($first->registeredChannels())->toBe(['order.{id}'])
        ->and($second->registeredChannels())->toBe(['order.{id}']);
});

test('a mercure authorization goes to the mercure connection', function (): void {
    $pusher = recordingBroadcaster();
    $mercure = mercureBroadcaster();
    $request = Request::create('/broadcasting/auth', 'POST', ['channel_names' => ['private-order.1']]);

    $mercure->shouldReceive('auth')->once()->with($request)->andReturn('mercure auth');

    expect((new CombinedBroadcaster([$pusher, $mercure]))->auth($request))->toBe('mercure auth')
        ->and($pusher->authRequests)->toBe([]);
});

test('a pusher authorization goes to the other connection', function (): void {
    $pusher = recordingBroadcaster();
    $mercure = mercureBroadcaster();
    $request = Request::create(
        '/broadcasting/auth',
        'POST',
        ['channel_name' => 'private-order.1', 'socket_id' => '1.1']
    );

    $mercure->shouldNotReceive('auth');

    (new CombinedBroadcaster([$mercure, $pusher]))->auth($request);

    expect($pusher->authRequests)->toBe([$request]);
});

test('the combined connection is built from the configured connections', function (): void {
    config([
        'broadcasting.connections.combined.connections' => ['log', 'null'],
    ]);

    $broadcaster = Broadcast::connection('combined');

    expect($broadcaster)->toBeInstanceOf(CombinedBroadcaster::class)
        ->and(array_map(get_class(...), $broadcaster->broadcasters()))
        ->toBe([
            Illuminate\Broadcasting\Broadcasters\LogBroadcaster::class,
            Illuminate\Broadcasting\Broadcasters\NullBroadcaster::class,
        ]);
});

test('the combined connection is available to a package that resolves broadcasting before flux boots', function (): void {
    config([
        'broadcasting.default' => 'combined',
        'broadcasting.connections.combined.connections' => ['log', 'null'],
    ]);

    app()->forgetInstance(Illuminate\Broadcasting\BroadcastManager::class);
    Broadcast::clearResolvedInstance(Illuminate\Contracts\Broadcasting\Factory::class);

    expect(app(Illuminate\Broadcasting\BroadcastManager::class)->connection())
        ->toBeInstanceOf(CombinedBroadcaster::class);
});
