<?php

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Models\Employee;
use FluxErp\Models\Language;
use FluxErp\Models\Permission;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\User;
use FluxErp\Models\WorkTimeModel;
use Illuminate\Support\Number;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->permission = Permission::findOrCreate(
        'api.widgets.current-work-time-model.get',
        'sanctum'
    );

    $this->workTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Standard 40h',
        'weekly_hours' => 40,
        'work_days_per_week' => 5,
        'annual_vacation_days' => 30,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'is_active' => true,
    ]);

    $this->employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
    ]);

    app(EmployeeWorkTimeModel::class)->create([
        'employee_id' => $this->employee->getKey(),
        'work_time_model_id' => $this->workTimeModel->getKey(),
        'valid_from' => now()->subYear(),
        'valid_until' => null,
    ]);
});

test('widget api returns the computed result for the given parameters', function (): void {
    $this->user->givePermissionTo($this->permission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->getJson(
        '/api/widgets/current-work-time-model?employeeId=' . $this->employee->getKey()
    );

    $response->assertOk();

    expect($response->json('data.sum'))->toEqual(Number::format(8, 2) . 'h')
        ->and($response->json('data.subValue'))->toContain('5');
});

test('widget api validates the request parameters', function (): void {
    $this->user->givePermissionTo($this->permission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->getJson('/api/widgets/current-work-time-model');

    $response->assertUnprocessable()
        ->assertJsonValidationErrorFor('employeeId');
});

test('widget api forbids users without the permission', function (): void {
    $otherUser = User::factory()->create([
        'language_id' => Language::factory()->create()->id,
    ]);
    Sanctum::actingAs($otherUser, ['user']);

    $response = $this->getJson('/api/widgets/current-work-time-model');

    $response->assertForbidden();
});
