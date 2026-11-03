<?php

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Enums\SalaryTypeEnum;
use FluxErp\Livewire\Widgets\HumanResources\PersonnelCostsBox;
use FluxErp\Livewire\Widgets\HumanResources\PersonnelCostsByDepartmentChart;
use FluxErp\Livewire\Widgets\HumanResources\PersonnelCostsPerEmployeeWidget;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
use Illuminate\Support\Number;
use Livewire\Livewire;

beforeEach(function (): void {
    $workTimeModel = app(WorkTimeModel::class)->create([
        'name' => 'Standard 40h',
        'weekly_hours' => 40,
        'work_days_per_week' => 5,
        'annual_vacation_days' => 30,
        'overtime_compensation' => OvertimeCompensationEnum::TimeOff,
        'is_active' => true,
    ]);

    $department = app(EmployeeDepartment::class)->create([
        'name' => 'Development',
        'is_active' => true,
    ]);

    $createEmployee = function (string $lastname, ?int $departmentId, array $attributes = []) use ($workTimeModel): Employee {
        $employee = app(Employee::class)->create(array_merge([
            'tenant_id' => $this->dbTenant->getKey(),
            'firstname' => 'Test',
            'lastname' => $lastname,
            'employee_department_id' => $departmentId,
            'is_active' => true,
            'employment_date' => now()->subYear(),
            'salary_type' => SalaryTypeEnum::Monthly,
            'base_salary' => 1000,
            'employer_contribution_rate' => 20,
        ], $attributes));

        app(EmployeeWorkTimeModel::class)->create([
            'employee_id' => $employee->getKey(),
            'work_time_model_id' => $workTimeModel->getKey(),
            'valid_from' => now()->subYear(),
        ]);

        return $employee;
    };

    $createEmployee('Developer', $department->getKey(), ['base_salary' => 3000]);
    $createEmployee('Assistant', null);
    $createEmployee('Former', $department->getKey(), ['termination_date' => now()->subMonth()]);
});

test('box sums the costs of employed employees', function (): void {
    Livewire::test(PersonnelCostsBox::class)
        ->assertOk()
        ->assertSet('sum', Number::currency(4800));
});

test('chart groups the costs by department', function (): void {
    Livewire::test(PersonnelCostsByDepartmentChart::class)
        ->assertOk()
        ->assertSet('xaxis.categories', ['Development', __('No Department')])
        ->assertSet('series.0.data', [3600, 1200]);
});

test('list shows the costs per employee', function (): void {
    Livewire::test(PersonnelCostsPerEmployeeWidget::class)
        ->assertOk()
        ->assertCount('employees', 2)
        ->assertSet('employees.0.name', 'Test Developer')
        ->assertSet('employees.0.total', Number::currency(3600));
});
