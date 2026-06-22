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

test('widget api returns the computed result', function (): void {
    $this->user->givePermissionTo($this->permission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->getJson('/api/widgets/current-work-time-model');

    $response->assertOk();

    expect($response->json('data.sum'))->toEqual(Number::format(8, 2) . 'h');
});

test('widget api is scoped to the calling user employee', function (): void {
    $this->user->givePermissionTo($this->permission);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->getJson('/api/widgets/current-work-time-model');

    $response->assertOk();

    expect($response->json('data.subValue'))->toContain('5');
});

test('widget api forbids users without the permission', function (): void {
    $otherUser = User::factory()->create([
        'language_id' => Language::factory()->create()->id,
    ]);
    Sanctum::actingAs($otherUser, ['user']);

    $response = $this->actingAs($otherUser)
        ->getJson('/api/widgets/current-work-time-model');

    $response->assertForbidden();
});
