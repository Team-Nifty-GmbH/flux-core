<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\WorkTime;
use FluxErp\Models\Task;
use FluxErp\Models\WorkTime as WorkTimeModel;
use FluxErp\Models\WorkTimeType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('can open work time modal on start', function (): void {
    $task = Task::factory()->create([
        'name' => Str::uuid()->toString(),
        'description' => Str::uuid()->toString(),
    ]);

    $data = [
        'name' => $task->name,
        'description' => $task->description,
        'trackable_type' => $task->getMorphClass(),
        'trackable_id' => $task->getKey(),
    ];

    Livewire::test(WorkTime::class)
        ->call('toggleWorkDay', true)
        ->call('start', $data)
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertSet('workTime.name', $data['name'])
        ->assertSet('workTime.description', $data['description'])
        ->assertSet('workTime.trackable_type', $data['trackable_type'])
        ->assertSet('workTime.trackable_id', $data['trackable_id'])
        ->call('save')
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertCount('activeWorkTimes', 1);
});

test('cant create billable task time without contact', function (): void {
    $workTimeType = WorkTimeType::factory()->create([
        'is_billable' => true,
    ]);

    Livewire::test(WorkTime::class)
        ->assertSet('activeWorkTimes', [])
        ->assertSet('dailyWorkTime.id', null)
        ->call('toggleWorkDay', true)
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->set('workTime.work_time_type_id', $workTimeType->getKey())
        ->set('workTime.is_billable', true)
        ->set('workTime.name', Str::uuid())
        ->call('save')
        ->assertStatus(200)
        ->assertHasErrors(['contact_id'])
        ->assertCount('activeWorkTimes', 0);
});

test('create task time', function (): void {
    $component = Livewire::test(WorkTime::class)
        ->assertSet('activeWorkTimes', [])
        ->assertSet('dailyWorkTime.id', null)
        ->call('toggleWorkDay', true)
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->set('workTime', [
            'name' => $workTimeName = Str::uuid(),
        ])
        ->call('save')
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertCount('activeWorkTimes', 1);

    $this->assertDatabaseHas(
        'work_times',
        [
            'user_id' => $this->user->getKey(),
            'name' => $workTimeName,
            'is_daily_work_time' => false,
            'is_pause' => false,
            'is_locked' => false,
        ]
    );

    $dbTaskTime = WorkTimeModel::query()
        ->where('name', $workTimeName)
        ->first();

    $this->travel(1)->hour();
    $component->call('pause', $dbTaskTime->getKey())
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertCount('activeWorkTimes', 1);

    $dbTaskTime->refresh();
    expect($dbTaskTime->ended_at)->not->toBeNull();
    expect($dbTaskTime->is_locked)->toBeFalse();
    expect($dbTaskTime->total_time_ms)->toEqual(0);
    expect($dbTaskTime->paused_time_ms)->toEqual(0);
    $dbPauseStartTime = $dbTaskTime->ended_at;

    $this->travel(1)->hour();
    $component->call('continue', $dbTaskTime->getKey())
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertCount('activeWorkTimes', 1);

    $dbTaskTime->refresh();

    expect($dbTaskTime->ended_at)->toBeNull();
    expect($dbTaskTime->is_locked)->toBeFalse();
    expect($dbTaskTime->total_time_ms)->toEqual(0);
    expect(bcround($dbTaskTime->paused_time_ms, 0))->toEqual(bcround($dbPauseStartTime->diffInMilliseconds($dbTaskTime->ended_at, true), 0));
    expect($dbTaskTime->paused_time_ms)->toBeGreaterThan(0);

    $this->travel(1)->hour();

    $component->call('stop', $dbTaskTime->getKey())
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertCount('activeWorkTimes', 0);

    $dbTaskTime->refresh();

    expect($dbTaskTime->ended_at)->not->toBeNull();
    expect($dbTaskTime->is_locked)->toBeTrue();
    expect($dbTaskTime->total_time_ms)->toBeGreaterThan(0);
    expect($dbTaskTime->total_time_ms)->toBeLessThan($dbTaskTime->started_at->diffInMilliseconds($dbTaskTime->ended_at));
    expect($dbTaskTime->total_time_ms)->toEqual(bcsub(
        $dbTaskTime->started_at->diffInMilliseconds($dbTaskTime->ended_at),
        $dbTaskTime->paused_time_ms,
        0
    ));
});

test('pause daily work time', function (): void {
    $this->travelTo(now()->startOfDay()->addHours(8));
    $workTime = WorkTimeModel::factory()->create([
        'user_id' => $this->user->getKey(),
        'started_at' => $startedAt = now(),
        'ended_at' => null,
        'is_daily_work_time' => true,
        'is_pause' => false,
        'is_locked' => false,
    ]);

    $this->travel(4)->hours();
    $component = Livewire::test(WorkTime::class)
        ->assertStatus(200)
        ->assertSet('dailyWorkTime.id', $workTime->getKey())
        ->assertSet('dailyWorkTime.user_id', $this->user->getKey())
        ->assertSet('dailyWorkTime.started_at', $startedAt->toISOString())
        ->assertSet('dailyWorkTime.ended_at', null)
        ->assertSet('dailyWorkTime.is_daily_work_time', true)
        ->call('togglePauseWorkDay', true)
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertNotSet('dailyWorkTimePause.id', null)
        ->assertSet('dailyWorkTimePause.user_id', $this->user->getKey())
        ->assertNotSet('dailyWorkTimePause.started_at', null)
        ->assertSet('dailyWorkTimePause.ended_at', null)
        ->assertSet('dailyWorkTimePause.is_daily_work_time', true)
        ->assertSet('dailyWorkTimePause.is_pause', true);

    $pauseStartedAt = $component->get('dailyWorkTimePause.started_at');
    $dbPauseWorkTimeId = $component->get('dailyWorkTimePause.id');

    $this->travel(1)->hour();
    $pauseEndedAt = now()->toDateTimeString();
    $component->call('togglePauseWorkDay', false)
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertSet('dailyWorkTimePause.id', null);

    $dbPauseWorkTime = WorkTimeModel::query()
        ->whereKey($dbPauseWorkTimeId)
        ->first();

    expect($dbPauseWorkTime)->not->toBeNull();
    expect($dbPauseWorkTime->user_id)->toEqual($this->user->getKey());
    expect($dbPauseWorkTime->started_at)->toEqual(Carbon::parse($pauseStartedAt));
    expect($dbPauseWorkTime->ended_at)->toEqual($pauseEndedAt);
    expect($dbPauseWorkTime->is_locked)->toBeTrue();
    expect($dbPauseWorkTime->is_daily_work_time)->toBeTrue();
    expect($dbPauseWorkTime->is_pause)->toBeTrue();
    expect($dbPauseWorkTime->total_time_ms)->toBeLessThan(0);

    expect($dbPauseWorkTime->total_time_ms)->toEqual(Carbon::parse($pauseEndedAt)->diffInMilliseconds($pauseStartedAt));
});

test('renders successfully', function (): void {
    Livewire::test(WorkTime::class)
        ->assertStatus(200);
});

test('toggle daily work time', function (): void {
    $component = Livewire::test(WorkTime::class)
        ->call('toggleWorkDay', true)
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertNotSet('dailyWorkTime.id', null)
        ->assertSet('dailyWorkTime.user_id', $this->user->getKey())
        ->assertSet('dailyWorkTime.is_daily_work_time', true)
        ->assertSet('dailyWorkTime.is_pause', false);

    $this->assertDatabaseHas(
        'work_times',
        [
            'user_id' => $this->user->getKey(),
            'started_at' => $startedAt = $component->get('dailyWorkTime.started_at'),
            'ended_at' => null,
            'is_locked' => false,
            'is_daily_work_time' => true,
            'is_pause' => false,
        ]
    );

    $dailyWorkTimeId = $component->get('dailyWorkTime.id');

    $this->travel(8)->hours();
    $component->call('toggleWorkDay', false)
        ->assertStatus(200)
        ->assertHasNoErrors()
        ->assertSet('dailyWorkTime.id', null)
        ->assertSet('dailyWorkTime.started_at', null)
        ->assertSet('dailyWorkTime.ended_at', null)
        ->assertSet('activeWorkTimes', []);

    $dbDailyWorkTime = WorkTimeModel::query()
        ->whereKey($dailyWorkTimeId)
        ->first();

    expect($dbDailyWorkTime)->not->toBeNull();
    expect($dbDailyWorkTime->user_id)->toEqual($this->user->getKey());
    expect($dbDailyWorkTime->started_at)->toEqual(Carbon::parse($startedAt));
    expect($dbDailyWorkTime->ended_at)->toEqual(now()->toDateTimeString());
    expect($dbDailyWorkTime->is_locked)->toBeTrue();
    expect($dbDailyWorkTime->is_daily_work_time)->toBeTrue();
    expect($dbDailyWorkTime->is_pause)->toBeFalse();
    expect($dbDailyWorkTime->total_time_ms)->toBeGreaterThan(0);

    expect(bcround($dbDailyWorkTime->total_time_ms, 0))->toEqual(bcround($dbDailyWorkTime->started_at->diffInMilliseconds($dbDailyWorkTime->ended_at), 0));
});
