<?php

use FluxErp\Models\LeadState;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    config()->set('queue.default', 'sync');
    config()->set('broadcasting.default', 'log');
});

test('force deleting a soft deleting model does not queue a broadcast', function (): void {
    $leadState = LeadState::factory()->create();

    $leadState->forceDelete();

    expect(LeadState::query()->withTrashed()->whereKey($leadState->getKey())->exists())->toBeFalse();
});

test('soft deleting and restoring still broadcast', function (): void {
    $leadState = LeadState::factory()->create();

    Queue::fake();

    $leadState->delete();

    $afterDelete = Queue::pushed(BroadcastEvent::class)->count();

    $leadState->restore();

    $afterRestore = Queue::pushed(BroadcastEvent::class)->count();

    expect($afterDelete)->toBeGreaterThan(0)
        ->and($afterRestore)->toBeGreaterThan($afterDelete)
        ->and(LeadState::query()->whereKey($leadState->getKey())->exists())->toBeTrue();
});
