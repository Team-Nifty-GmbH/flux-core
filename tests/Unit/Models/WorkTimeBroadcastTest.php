<?php

use FluxErp\Models\User;
use FluxErp\Models\WorkTime;
use Illuminate\Support\Arr;

function workTimeBroadcastChannels(WorkTime $workTime, string $event): array
{
    return collect(Arr::wrap($workTime->broadcastOn($event)))
        ->map(fn ($channel) => $channel->name)
        ->all();
}

test('work time broadcasts on the owning user channel', function (): void {
    $user = User::factory()->create();
    $workTime = WorkTime::factory()->make(['user_id' => $user->getKey()]);

    expect(workTimeBroadcastChannels($workTime, 'updated'))
        ->toContain('private-user.' . $user->getKey());
});

test('work time does not broadcast on another user channel', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $workTime = WorkTime::factory()->make(['user_id' => $owner->getKey()]);

    expect(workTimeBroadcastChannels($workTime, 'updated'))
        ->not->toContain('private-user.' . $other->getKey());
});

test('work time without a user is not broadcast on a user channel', function (): void {
    $workTime = WorkTime::factory()->make(['user_id' => null]);

    expect(workTimeBroadcastChannels($workTime, 'updated'))
        ->each->not->toStartWith('private-user.');
});
