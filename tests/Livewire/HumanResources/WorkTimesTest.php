<?php

use FluxErp\Livewire\HumanResources\WorkTimes;
use FluxErp\Models\WorkTime;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(WorkTimes::class)
        ->assertOk();
});

test('edit with new work time resets form', function (): void {
    Livewire::test(WorkTimes::class)
        ->call('edit', app(WorkTime::class))
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('workTime.id', null)
        ->assertSet('workTime.user_id', null)
        ->assertSet('workTime.started_at', null)
        ->assertSet('workTime.ended_at', null);
});

test('edit with existing work time fills form', function (): void {
    $workTime = app(WorkTime::class)->create([
        'user_id' => $this->user->getKey(),
        'started_at' => now()->subHours(2)->format('Y-m-d H:i:s'),
        'ended_at' => now()->subHour()->format('Y-m-d H:i:s'),
        'name' => 'Test Work',
        'is_locked' => true,
        'is_daily_work_time' => false,
    ]);

    Livewire::test(WorkTimes::class)
        ->call('edit', $workTime)
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('workTime.id', $workTime->getKey())
        ->assertSet('workTime.name', 'Test Work');
});

test('can create locked work time via save', function (): void {
    $startedAt = now()->subHours(3)->format('Y-m-d H:i:s');
    $endedAt = now()->subHour()->format('Y-m-d H:i:s');

    Livewire::test(WorkTimes::class)
        ->call('edit', app(WorkTime::class))
        ->set('workTime.user_id', $this->user->getKey())
        ->set('workTime.started_at', $startedAt)
        ->set('workTime.ended_at', $endedAt)
        ->set('workTime.name', 'Created Work Time')
        ->set('workTime.is_daily_work_time', false)
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('work_times', [
        'user_id' => $this->user->getKey(),
        'name' => 'Created Work Time',
        'is_locked' => true,
    ]);
});

test('can update locked work time via save', function (): void {
    $workTime = app(WorkTime::class)->create([
        'user_id' => $this->user->getKey(),
        'started_at' => now()->subHours(2)->format('Y-m-d H:i:s'),
        'ended_at' => now()->subHour()->format('Y-m-d H:i:s'),
        'name' => 'Original Work Time',
        'is_locked' => true,
        'is_daily_work_time' => false,
        'paused_time_ms' => 0,
        'is_billable' => false,
    ]);

    Livewire::test(WorkTimes::class)
        ->call('edit', $workTime)
        ->set('workTime.name', 'Updated Work Time')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    expect($workTime->refresh()->name)->toEqual('Updated Work Time');
});

test('can delete work time', function (): void {
    $workTime = app(WorkTime::class)->create([
        'user_id' => $this->user->getKey(),
        'started_at' => now()->subHours(2)->format('Y-m-d H:i:s'),
        'ended_at' => now()->subHour()->format('Y-m-d H:i:s'),
        'name' => 'To Delete',
        'is_locked' => true,
        'is_daily_work_time' => false,
    ]);

    Livewire::test(WorkTimes::class)
        ->call('delete', $workTime)
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertSoftDeleted('work_times', [
        'id' => $workTime->getKey(),
    ]);
});

test('save validates required fields', function (): void {
    Livewire::test(WorkTimes::class)
        ->call('edit', app(WorkTime::class))
        ->set('workTime.user_id', null)
        ->set('workTime.started_at', null)
        ->call('save')
        ->assertReturned(false);
});
