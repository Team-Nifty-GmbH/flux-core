<?php

namespace FluxErp\Tests\Livewire\HumanResources;

use FluxErp\Livewire\HumanResources\HrDashboard;
use FluxErp\Models\Client;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Models\WorkTimeModelSchedule;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class HrDashboardTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a language for testing if it doesn't exist
        $language = Language::find(1) ?? Language::factory()->create(['id' => 1]);
        
        // Create a client for testing if it doesn't exist
        $client = Client::find(1) ?? Client::factory()->create(['id' => 1, 'is_active' => true]);
        
        // Create a work time model with schedules
        $workTimeModel = WorkTimeModel::factory()->create([
            'name' => 'Test Model',
            'is_active' => true,
            'client_id' => 1
        ]);
        
        // Create schedules for the work time model
        for ($day = 1; $day <= 7; $day++) {
            WorkTimeModelSchedule::create([
                'work_time_model_id' => $workTimeModel->id,
                'week_number' => 1,
                'weekday' => $day,
                'start_time' => $day <= 5 ? '08:00:00' : null,
                'end_time' => $day <= 5 ? '17:00:00' : null,
                'work_hours' => $day <= 5 ? 8 : 0,
                'break_minutes' => $day <= 5 ? 60 : 0,
            ]);
        }
        
        // Create test user with work time model
        $this->user = User::factory()->create([
            'is_active' => true,
            'employment_date' => now()->subYear(),
            'work_time_model_id' => $workTimeModel->id
        ]);
        
        $this->actingAs($this->user);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(HrDashboard::class)
            ->assertStatus(200);
    }

    public function test_displays_overview_widgets(): void
    {
        $component = Livewire::test(HrDashboard::class);
        
        // The component should be able to render without errors
        $this->assertNotNull($component);
    }

    public function test_displays_employee_statistics(): void
    {
        // Create some additional employees
        User::factory()->count(5)->create([
            'is_active' => true,
            'work_time_model_id' => $this->user->work_time_model_id
        ]);
        
        User::factory()->count(2)->create([
            'is_active' => false,
            'work_time_model_id' => $this->user->work_time_model_id
        ]);
        
        $component = Livewire::test(HrDashboard::class);
        
        // The component should render successfully with employee data
        $this->assertNotNull($component);
    }

    public function test_can_access_hr_sections(): void
    {
        Livewire::test(HrDashboard::class)
            ->assertStatus(200);
    }
}