<?php

use FluxErp\Models\LeadState;
use FluxErp\Providers\BroadcastServiceProvider;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Contracts\Broadcasting\Broadcaster;
use Illuminate\Support\Defer\DeferredCallbackCollection;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Queue;

function captureBroadcasts(ArrayObject $captured): void
{
    Broadcast::extend('capture', fn (): Broadcaster => new class($captured) implements Broadcaster
    {
        public function __construct(protected ArrayObject $captured) {}

        public function auth($request): void {}

        public function validAuthenticationResponse($request, $result): void {}

        public function broadcast(array $channels, $event, array $payload = []): void
        {
            $this->captured[] = [
                'channels' => array_values(array_map('strval', $channels)),
                'event' => $event,
                'payload' => $payload,
            ];
        }
    });

    config()->set('broadcasting.connections.capture', ['driver' => 'capture']);
    config()->set('broadcasting.default', 'capture');
}

beforeEach(function (): void {
    config()->set('queue.default', 'sync');
});

test('registers its own deferred queue connection', function (): void {
    expect(config('queue.connections.' . BroadcastServiceProvider::QUEUE_CONNECTION))
        ->toBe(['driver' => 'deferred']);
});

test('model broadcasts use the deferred connection', function (): void {
    expect(LeadState::factory()->make()->broadcastConnection())
        ->toBe(BroadcastServiceProvider::QUEUE_CONNECTION);
});

test('force deleting a soft deleting model reaches the broadcaster with the record key', function (): void {
    $captured = new ArrayObject();
    captureBroadcasts($captured);

    $leadState = LeadState::factory()->create();
    $key = $leadState->getKey();
    $captured->exchangeArray([]);

    $leadState->forceDelete();

    expect(LeadState::query()->withTrashed()->whereKey($key)->exists())->toBeFalse();

    app(DeferredCallbackCollection::class)->invoke();

    $deleted = collect($captured->getArrayCopy())
        ->firstWhere('event', 'LeadStateDeleted');

    expect($deleted)->not->toBeNull()
        ->and($deleted['payload']['model'])->toBe(['id' => $key])
        ->and(implode(',', $deleted['channels']))->toContain(morph_alias(LeadState::class) . '.' . $key);
});

test('soft deleting and restoring still broadcast', function (): void {
    config()->set('broadcasting.default', 'log');

    $leadState = LeadState::factory()->create();

    Queue::fake();

    $leadState->delete();

    $afterDelete = Queue::pushed(BroadcastEvent::class)->count();

    $leadState->restore();

    expect($afterDelete)->toBeGreaterThan(0)
        ->and(Queue::pushed(BroadcastEvent::class)->count())->toBeGreaterThan($afterDelete)
        ->and(LeadState::query()->whereKey($leadState->getKey())->exists())->toBeTrue();
});
