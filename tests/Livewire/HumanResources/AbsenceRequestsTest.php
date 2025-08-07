<?php

namespace FluxErp\Tests\Livewire\HumanResources;

use FluxErp\Livewire\HumanResources\AbsenceRequests;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Client;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class AbsenceRequestsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create language and client for testing
        $language = Language::find(1) ?? Language::factory()->create(['id' => 1]);
        $client = Client::find(1) ?? Client::factory()->create(['id' => 1, 'is_active' => true]);
        
        // Create absence type
        $this->absenceType = AbsenceType::factory()->create([
            'name' => 'Vacation',
            'is_vacation' => true,
            'is_active' => true,
            'client_id' => 1
        ]);
        
        // Create work time model
        $this->workTimeModel = WorkTimeModel::factory()->create([
            'name' => 'Full Time',
            'annual_vacation_days' => 25,
            'is_active' => true,
            'client_id' => 1
        ]);
        
        // Create test user
        $this->user = User::factory()->create([
            'is_active' => true,
            'employment_date' => now()->subYear(),
            'work_time_model_id' => $this->workTimeModel->id,
            'vacation_days_current' => 25,
            'vacation_days_carried' => 0
        ]);
        
        $this->actingAs($this->user);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(AbsenceRequests::class)
            ->assertStatus(200);
    }

    public function test_can_switch_tabs(): void
    {
        Livewire::test(AbsenceRequests::class)
            ->assertSet('viewType', 'my')
            ->set('viewType', 'team')
            ->assertSet('viewType', 'team')
            ->set('viewType', 'approval')
            ->assertSet('viewType', 'approval');
    }

    public function test_can_open_new_request_modal(): void
    {
        Livewire::test(AbsenceRequests::class)
            ->call('edit')
            ->assertSet('absenceRequestForm.user_id', $this->user->id);
    }

    public function test_can_create_absence_request(): void
    {
        Livewire::test(AbsenceRequests::class)
            ->call('edit')
            ->set('absenceRequestForm.user_id', $this->user->id)
            ->set('absenceRequestForm.absence_type_id', $this->absenceType->id)
            ->set('absenceRequestForm.start_date', now()->addWeek()->format('Y-m-d'))
            ->set('absenceRequestForm.end_date', now()->addWeek()->addDays(4)->format('Y-m-d'))
            ->set('absenceRequestForm.days_requested', 5)
            ->set('absenceRequestForm.reason', 'Test absence request')
            ->call('save');
            
        $this->assertDatabaseHas('absence_requests', [
            'user_id' => $this->user->id,
            'absence_type_id' => $this->absenceType->id,
            'reason' => 'Test absence request'
        ]);
    }

    public function test_can_edit_existing_request(): void
    {
        $request = AbsenceRequest::factory()->create([
            'user_id' => $this->user->id,
            'absence_type_id' => $this->absenceType->id,
            'status' => 'draft'
        ]);
        
        Livewire::test(AbsenceRequests::class)
            ->call('edit', $request->id)
            ->assertSet('absenceRequestForm.id', $request->id)
            ->assertSet('absenceRequestForm.user_id', $request->user_id);
    }

    public function test_can_delete_draft_request(): void
    {
        $request = AbsenceRequest::factory()->create([
            'user_id' => $this->user->id,
            'absence_type_id' => $this->absenceType->id,
            'status' => 'draft'
        ]);
        
        Livewire::test(AbsenceRequests::class)
            ->call('delete', $request->id);
            
        $this->assertSoftDeleted('absence_requests', ['id' => $request->id]);
    }

    public function test_supervisor_can_approve_request(): void
    {
        // Create supervisor user
        $supervisor = User::factory()->create(['is_active' => true]);
        $this->user->update(['supervisor_id' => $supervisor->id]);
        
        $request = AbsenceRequest::factory()->create([
            'user_id' => $this->user->id,
            'absence_type_id' => $this->absenceType->id,
            'status' => 'pending'
        ]);
        
        $this->actingAs($supervisor);
        
        Livewire::test(AbsenceRequests::class)
            ->call('approve', $request->id);
            
        $this->assertDatabaseHas('absence_requests', [
            'id' => $request->id,
            'status' => 'approved',
            'approved_by' => $supervisor->id
        ]);
    }

    public function test_supervisor_can_reject_request(): void
    {
        // Create supervisor user
        $supervisor = User::factory()->create(['is_active' => true]);
        $this->user->update(['supervisor_id' => $supervisor->id]);
        
        $request = AbsenceRequest::factory()->create([
            'user_id' => $this->user->id,
            'absence_type_id' => $this->absenceType->id,
            'status' => 'pending'
        ]);
        
        $this->actingAs($supervisor);
        
        Livewire::test(AbsenceRequests::class)
            ->set('rejectionReason', 'Not enough coverage')
            ->call('reject', $request->id);
            
        $this->assertDatabaseHas('absence_requests', [
            'id' => $request->id,
            'status' => 'rejected',
            'approved_by' => $supervisor->id,
            'rejection_reason' => 'Not enough coverage'
        ]);
    }
}