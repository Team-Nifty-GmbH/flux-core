<?php

use FluxErp\Actions\LeadState\CreateLeadState;
use FluxErp\Events\BroadcastableActionEventOccurred;
use FluxErp\Models\LeadState;
use FluxErp\Traits\Action\BroadcastsActionEvents;
use Illuminate\Contracts\Broadcasting\Broadcaster;
use Illuminate\Support\Defer\DeferredCallbackCollection;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Queue;

class BroadcastingCreateLeadState extends CreateLeadState
{
    use BroadcastsActionEvents;
}

$captureBroadcasts = function (ArrayObject $captured): void {
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
};

$invokeDeferred = fn () => app(DeferredCallbackCollection::class)->invoke();

beforeEach(function (): void {
    config()->set('queue.default', 'sync');
});

test('model broadcasts never reach the queue', function () use ($captureBroadcasts, $invokeDeferred): void {
    $captured = new ArrayObject();
    $captureBroadcasts($captured);

    Queue::fake();

    $leadState = LeadState::factory()->create();
    $leadState->update(['name' => 'renamed for the docs']);
    $leadState->delete();

    $invokeDeferred();

    Queue::assertNothingPushed();

    expect(collect($captured->getArrayCopy())->pluck('event'))
        ->toContain('LeadStateCreated', 'LeadStateUpdated', 'LeadStateTrashed');
});

test('force deleting reaches the broadcaster with the record key', function () use ($captureBroadcasts, $invokeDeferred): void {
    $captured = new ArrayObject();
    $captureBroadcasts($captured);

    $leadState = LeadState::factory()->create();
    $key = $leadState->getKey();
    $captured->exchangeArray([]);

    $leadState->forceDelete();

    expect(LeadState::query()->withTrashed()->whereKey($key)->exists())->toBeFalse();

    $invokeDeferred();

    $deleted = collect($captured->getArrayCopy())->firstWhere('event', 'LeadStateDeleted');

    expect($deleted)->not->toBeNull()
        ->and($deleted['payload']['model'])->toBe(['id' => $key])
        ->and(implode(',', $deleted['channels']))->toContain(morph_alias(LeadState::class) . '.' . $key);
});

test('the broadcast leaves only after the response', function () use ($captureBroadcasts, $invokeDeferred): void {
    $captured = new ArrayObject();
    $captureBroadcasts($captured);

    LeadState::factory()->create();

    expect($captured->count())->toBe(0);

    $invokeDeferred();

    expect($captured->count())->toBeGreaterThan(0);
});

test('action broadcasts leave without a queue job', function () use ($captureBroadcasts): void {
    $captured = new ArrayObject();
    $captureBroadcasts($captured);

    Queue::fake();

    $action = BroadcastingCreateLeadState::make(['name' => 'created through an action']);

    $broadcastable = new BroadcastableActionEventOccurred($action, 'executed');

    expect($broadcastable->shouldBroadcastNow())->toBeTrue();

    broadcast($broadcastable);

    Queue::assertNothingPushed();

    expect(collect($captured->getArrayCopy())->pluck('event'))
        ->toContain('BroadcastingCreateLeadStateExecuted');
});
