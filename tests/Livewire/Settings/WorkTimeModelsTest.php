<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\WorkTimeModels;
use FluxErp\Models\Client;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Models\WorkTimeModelSchedule;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class WorkTimeModelsTest extends BaseSetup
{
    protected string $livewireComponent = WorkTimeModels::class;
    
    protected ?Client $client = null;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a client for testing if it doesn't exist
        $this->client = Client::find(1) ?? Client::factory()->create(['id' => 1, 'is_active' => true]);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }

    public function test_can_create_work_time_model(): void
    {
        Livewire::test($this->livewireComponent)
            ->call('edit')
            ->set('workTimeModelForm.name', 'Test Work Time Model')
            ->set('workTimeModelForm.cycle_weeks', 1)
            ->set('workTimeModelForm.weekly_hours', 40)
            ->set('workTimeModelForm.annual_vacation_days', 25)
            ->set('workTimeModelForm.overtime_compensation', 'time_off')
            ->set('workTimeModelForm.is_active', true)
            ->set('workTimeModelForm.client_id', 1)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('work_time_models', [
            'name' => 'Test Work Time Model',
            'weekly_hours' => 40,
            'annual_vacation_days' => 25
        ]);
    }

    public function test_can_edit_existing_work_time_model(): void
    {
        $model = WorkTimeModel::factory()->create([
            'name' => 'Original Name',
            'is_active' => true
        ]);

        Livewire::test($this->livewireComponent)
            ->call('edit', $model->id)
            ->assertSet('workTimeModelForm.name', 'Original Name')
            ->set('workTimeModelForm.name', 'Updated Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('work_time_models', [
            'id' => $model->id,
            'name' => 'Updated Name'
        ]);
    }

    public function test_creates_schedules_with_work_time_model(): void
    {
        Livewire::test($this->livewireComponent)
            ->call('edit')
            ->set('workTimeModelForm.name', 'Model with Schedules')
            ->set('workTimeModelForm.cycle_weeks', 1)
            ->set('workTimeModelForm.weekly_hours', 40)
            ->set('workTimeModelForm.annual_vacation_days', 25)
            ->set('workTimeModelForm.client_id', 1)
            ->set('workTimeModelForm.schedules', [
                [
                    'week_number' => 1,
                    'days' => [
                        1 => ['weekday' => 1, 'start_time' => '08:00', 'end_time' => '17:00', 'work_hours' => 8, 'break_minutes' => 60],
                        2 => ['weekday' => 2, 'start_time' => '08:00', 'end_time' => '17:00', 'work_hours' => 8, 'break_minutes' => 60],
                        3 => ['weekday' => 3, 'start_time' => '08:00', 'end_time' => '17:00', 'work_hours' => 8, 'break_minutes' => 60],
                        4 => ['weekday' => 4, 'start_time' => '08:00', 'end_time' => '17:00', 'work_hours' => 8, 'break_minutes' => 60],
                        5 => ['weekday' => 5, 'start_time' => '08:00', 'end_time' => '17:00', 'work_hours' => 8, 'break_minutes' => 60],
                        6 => ['weekday' => 6, 'start_time' => null, 'end_time' => null, 'work_hours' => 0, 'break_minutes' => 0],
                        7 => ['weekday' => 7, 'start_time' => null, 'end_time' => null, 'work_hours' => 0, 'break_minutes' => 0],
                    ]
                ]
            ])
            ->call('save')
            ->assertHasNoErrors();

        $model = WorkTimeModel::where('name', 'Model with Schedules')->first();
        $this->assertNotNull($model);
        $this->assertEquals(7, $model->schedules()->count());
    }

    public function test_can_delete_work_time_model(): void
    {
        $model = WorkTimeModel::factory()->create();

        Livewire::test($this->livewireComponent)
            ->call('delete', $model->id);

        $this->assertSoftDeleted('work_time_models', ['id' => $model->id]);
    }

    public function test_validates_required_fields(): void
    {
        $initialCount = WorkTimeModel::count();
        
        Livewire::test($this->livewireComponent)
            ->call('edit')
            ->set('workTimeModelForm.name', '')
            ->set('workTimeModelForm.weekly_hours', null)
            ->set('workTimeModelForm.annual_vacation_days', null)
            ->call('save');
            
        // Verify no model was created due to validation failure
        $this->assertEquals($initialCount, WorkTimeModel::count());
    }

    public function test_creates_multi_week_schedules(): void
    {
        // Test creating a work time model with 2-week cycle
        Livewire::test($this->livewireComponent)
            ->call('edit')
            ->set('workTimeModelForm.name', 'Two Week Cycle')
            ->set('workTimeModelForm.cycle_weeks', 2)
            ->set('workTimeModelForm.weekly_hours', 40)
            ->set('workTimeModelForm.annual_vacation_days', 25)
            ->set('workTimeModelForm.client_id', 1)
            ->set('workTimeModelForm.schedules', [
                [
                    'week_number' => 1,
                    'days' => [
                        1 => ['weekday' => 1, 'start_time' => '08:00', 'end_time' => '17:00', 'work_hours' => 8, 'break_minutes' => 60],
                        2 => ['weekday' => 2, 'start_time' => '08:00', 'end_time' => '17:00', 'work_hours' => 8, 'break_minutes' => 60],
                        3 => ['weekday' => 3, 'start_time' => '08:00', 'end_time' => '17:00', 'work_hours' => 8, 'break_minutes' => 60],
                        4 => ['weekday' => 4, 'start_time' => '08:00', 'end_time' => '17:00', 'work_hours' => 8, 'break_minutes' => 60],
                        5 => ['weekday' => 5, 'start_time' => '08:00', 'end_time' => '17:00', 'work_hours' => 8, 'break_minutes' => 60],
                        6 => ['weekday' => 6, 'start_time' => null, 'end_time' => null, 'work_hours' => 0, 'break_minutes' => 0],
                        7 => ['weekday' => 7, 'start_time' => null, 'end_time' => null, 'work_hours' => 0, 'break_minutes' => 0],
                    ]
                ],
                [
                    'week_number' => 2,
                    'days' => [
                        1 => ['weekday' => 1, 'start_time' => '07:00', 'end_time' => '16:00', 'work_hours' => 8, 'break_minutes' => 60],
                        2 => ['weekday' => 2, 'start_time' => '07:00', 'end_time' => '16:00', 'work_hours' => 8, 'break_minutes' => 60],
                        3 => ['weekday' => 3, 'start_time' => '07:00', 'end_time' => '16:00', 'work_hours' => 8, 'break_minutes' => 60],
                        4 => ['weekday' => 4, 'start_time' => '07:00', 'end_time' => '16:00', 'work_hours' => 8, 'break_minutes' => 60],
                        5 => ['weekday' => 5, 'start_time' => '07:00', 'end_time' => '16:00', 'work_hours' => 8, 'break_minutes' => 60],
                        6 => ['weekday' => 6, 'start_time' => null, 'end_time' => null, 'work_hours' => 0, 'break_minutes' => 0],
                        7 => ['weekday' => 7, 'start_time' => null, 'end_time' => null, 'work_hours' => 0, 'break_minutes' => 0],
                    ]
                ]
            ])
            ->call('save')
            ->assertHasNoErrors();

        $model = WorkTimeModel::where('name', 'Two Week Cycle')->first();
        $this->assertNotNull($model);
        $this->assertEquals(2, $model->cycle_weeks);
        $this->assertEquals(14, $model->schedules()->count()); // 7 days * 2 weeks
        
        // Verify we have schedules for both weeks
        $week1Schedules = $model->schedules()->where('week_number', 1)->count();
        $week2Schedules = $model->schedules()->where('week_number', 2)->count();
        
        $this->assertEquals(7, $week1Schedules, 'Week 1 should have 7 days');
        $this->assertEquals(7, $week2Schedules, 'Week 2 should have 7 days');
    }

    public function test_can_toggle_core_hours(): void
    {
        Livewire::test($this->livewireComponent)
            ->call('edit')
            ->set('workTimeModelForm.has_core_hours', true)
            ->set('workTimeModelForm.core_hours_start', '09:00')
            ->set('workTimeModelForm.core_hours_end', '15:00')
            ->set('workTimeModelForm.name', 'Model with Core Hours')
            ->set('workTimeModelForm.weekly_hours', 40)
            ->set('workTimeModelForm.annual_vacation_days', 25)
            ->set('workTimeModelForm.client_id', 1)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('work_time_models', [
            'name' => 'Model with Core Hours',
            'has_core_hours' => true,
            'core_hours_start' => '09:00:00',
            'core_hours_end' => '15:00:00'
        ]);
    }
}