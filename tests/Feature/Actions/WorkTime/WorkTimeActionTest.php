<?php

use FluxErp\Actions\WorkTime\CreateWorkTime;
use FluxErp\Actions\WorkTime\DeleteWorkTime;
use FluxErp\Actions\WorkTime\UpdateWorkTime;
use FluxErp\Models\WorkTime;

test('create work time with time range', function (): void {
    $wt = CreateWorkTime::make([
        'user_id' => $this->user->getKey(),
        'name' => 'Development',
        'started_at' => '2026-04-05 08:00:00',
        'ended_at' => '2026-04-05 16:00:00',
    ])->validate()->execute();

    expect($wt)->toBeInstanceOf(WorkTime::class);
});

test('create daily work time', function (): void {
    $wt = CreateWorkTime::make([
        'user_id' => $this->user->getKey(),
        'is_daily_work_time' => true,
    ])->validate()->execute();

    expect($wt->is_daily_work_time)->toBeTruthy();
});

test('update work time', function (): void {
    $wt = CreateWorkTime::make([
        'user_id' => $this->user->getKey(),
        'name' => 'Original',
        'started_at' => '2026-04-05 09:00:00',
        'ended_at' => '2026-04-05 17:00:00',
    ])->validate()->execute();

    $updated = UpdateWorkTime::make([
        'id' => $wt->getKey(),
        'description' => 'Updated description',
        'is_locked' => false,
    ])->validate()->execute();

    expect($updated->description)->toBe('Updated description');
});

test('delete work time', function (): void {
    $wt = CreateWorkTime::make([
        'user_id' => $this->user->getKey(),
        'name' => 'Temp',
        'started_at' => '2026-04-05 10:00:00',
        'ended_at' => '2026-04-05 11:00:00',
    ])->validate()->execute();

    expect(DeleteWorkTime::make(['id' => $wt->getKey()])
        ->validate()->execute())->toBeTrue();
});
