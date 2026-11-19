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

test('running pause of one user does not block a pause for another user', function (): void {
    $employee = FluxErp\Models\User::factory()->create();

    $employeeWorkTime = WorkTime::factory()->create([
        'user_id' => $employee->getKey(),
        'ended_at' => null,
        'is_daily_work_time' => true,
        'is_pause' => false,
        'is_locked' => false,
    ]);

    // running pause of the terminal account, which must not block the employee
    WorkTime::factory()->create([
        'user_id' => $this->user->getKey(),
        'ended_at' => null,
        'is_daily_work_time' => true,
        'is_pause' => true,
        'is_locked' => false,
    ]);

    $pause = CreateWorkTime::make([
        'user_id' => $employee->getKey(),
        'parent_id' => $employeeWorkTime->getKey(),
        'started_at' => now()->subMinutes(5)->toDateTimeString(),
        'is_daily_work_time' => true,
        'is_locked' => false,
        'is_pause' => true,
    ])->validate()->execute();

    expect($pause->user_id)->toBe($employee->getKey());
});
