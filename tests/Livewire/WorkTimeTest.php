<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Livewire\WorkTime;
use FluxErp\Models\WorkTime as WorkTimeModel;
use FluxErp\Models\WorkTimeType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;

class WorkTimeTest extends BaseSetup
{
    public function test_renders_successfully()
    {
        Livewire::test(WorkTime::class)
            ->assertStatus(200);
    }

    public function test_toggle_daily_work_time()
    {
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

        $this->assertNotNull($dbDailyWorkTime);
        $this->assertEquals($this->user->getKey(), $dbDailyWorkTime->user_id);
        $this->assertEquals(Carbon::parse($startedAt), $dbDailyWorkTime->started_at);
        $this->assertEquals(now()->toDateTimeString(), $dbDailyWorkTime->ended_at);
        $this->assertTrue($dbDailyWorkTime->is_locked);
        $this->assertTrue($dbDailyWorkTime->is_daily_work_time);
        $this->assertFalse($dbDailyWorkTime->is_pause);
        $this->assertGreaterThan(0, $dbDailyWorkTime->total_time_ms);

        $this->assertEquals(
            bcround($dbDailyWorkTime->started_at->diffInMilliseconds($dbDailyWorkTime->ended_at), 0),
            bcround($dbDailyWorkTime->total_time_ms, 0)
        );
    }

    public function test_pause_daily_work_time()
    {
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

        $this->assertNotNull($dbPauseWorkTime);
        $this->assertEquals($this->user->getKey(), $dbPauseWorkTime->user_id);
        $this->assertEquals(Carbon::parse($pauseStartedAt), $dbPauseWorkTime->started_at);
        $this->assertEquals($pauseEndedAt, $dbPauseWorkTime->ended_at);
        $this->assertTrue($dbPauseWorkTime->is_locked);
        $this->assertTrue($dbPauseWorkTime->is_daily_work_time);
        $this->assertTrue($dbPauseWorkTime->is_pause);
        $this->assertLessThan(0, $dbPauseWorkTime->total_time_ms);

        $this->assertEquals(
            Carbon::parse($pauseEndedAt)->diffInMilliseconds($pauseStartedAt),
            $dbPauseWorkTime->total_time_ms
        );
    }

    public function test_create_task_time()
    {
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
        $this->assertNotNull($dbTaskTime->ended_at);
        $this->assertFalse($dbTaskTime->is_locked);
        $this->assertEquals(0, $dbTaskTime->total_time_ms);
        $this->assertEquals(0, $dbTaskTime->paused_time_ms);
        $dbPauseStartTime = $dbTaskTime->ended_at;

        $this->travel(1)->hour();
        $component->call('continue', $dbTaskTime->getKey())
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertCount('activeWorkTimes', 1);

        $dbTaskTime->refresh();

        $this->assertNull($dbTaskTime->ended_at);
        $this->assertFalse($dbTaskTime->is_locked);
        $this->assertEquals(0, $dbTaskTime->total_time_ms);
        $this->assertEquals(
            bcround($dbPauseStartTime->diffInMilliseconds($dbTaskTime->ended_at, true), 0),
            bcround($dbTaskTime->paused_time_ms, 0)
        );
        $this->assertGreaterThan(0, $dbTaskTime->paused_time_ms);

        $this->travel(1)->hour();

        $component->call('stop', $dbTaskTime->getKey())
            ->assertStatus(200)
            ->assertHasNoErrors()
            ->assertCount('activeWorkTimes', 0);

        $dbTaskTime->refresh();

        $this->assertNotNull($dbTaskTime->ended_at);
        $this->assertTrue($dbTaskTime->is_locked);
        $this->assertGreaterThan(0, $dbTaskTime->total_time_ms);
        $this->assertLessThan(
            $dbTaskTime->started_at->diffInMilliseconds($dbTaskTime->ended_at),
            $dbTaskTime->total_time_ms
        );
        $this->assertEquals(
            bcsub(
                $dbTaskTime->started_at->diffInMilliseconds($dbTaskTime->ended_at),
                $dbTaskTime->paused_time_ms,
                0
            ),
            $dbTaskTime->total_time_ms
        );
    }

    public function test_cant_create_billable_task_time_without_contact()
    {
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
    }
}
