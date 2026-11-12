<?php

use FluxErp\Enums\SalaryTypeEnum;
use FluxErp\Models\Employee;
use Illuminate\Support\Number;

test('hr dashboard loads without js errors', function (): void {
    visit(route('human-resources.dashboard'))
        ->assertNoSmoke();
});

test('employees list loads without js errors', function (): void {
    visit(route('human-resources.employees'))
        ->assertNoSmoke();
});

test('employees list shows data table', function (): void {
    visit(route('human-resources.employees'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('work times page loads without js errors', function (): void {
    visit(route('human-resources.work-times'))
        ->assertNoSmoke();
});

test('work times shows data table', function (): void {
    visit(route('human-resources.work-times'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('absence requests page loads without js errors', function (): void {
    visit(route('human-resources.absence-requests'))
        ->assertNoSmoke();
});

test('absence requests shows data table', function (): void {
    visit(route('human-resources.absence-requests'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('attendance overview page loads without js errors', function (): void {
    visit(route('human-resources.attendance-overview'))
        ->assertNoSmoke();
});

test('employee days page loads without js errors', function (): void {
    visit(route('human-resources.employee-days'))
        ->assertNoSmoke();
});

test('employee days shows data table', function (): void {
    visit(route('human-resources.employee-days'))
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('salary tab saves the salary and a component and updates the costs', function (): void {
    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
        'employment_date' => now()->subYear(),
        'salary_type' => SalaryTypeEnum::Monthly,
        'base_salary' => 2000,
        'employer_contribution_rate' => 10,
    ]);

    $page = visit(route('human-resources.employees.id', ['id' => $employee->getKey(), 'tab' => 'employee.salary']))
        ->assertNoSmoke();

    $total = json_encode(Number::currency(2200));
    waitForCondition($page, <<<JS
        () => document.querySelector('[x-text="\$wire.costs.total"]')?.textContent.trim() === {$total}
    JS, 10000);

    $page->script(<<<'JS'
        () => {
            const component = Livewire.all().find((c) => c.name === 'employee.salary');
            component.$wire.employeeSalaryForm.base_salary = 2500;
            document.querySelector('[wire\\:click="saveSalary()"]').click();
        }
    JS);

    $total = json_encode(Number::currency(2750));
    waitForCondition($page, <<<JS
        () => document.querySelector('[x-text="\$wire.costs.total"]')?.textContent.trim() === {$total}
    JS, 10000);

    $page->script(<<<'JS'
        () => Livewire.all().find((c) => c.name === 'employee.salary').$wire.edit()
    JS);

    waitForCondition($page, <<<'JS'
        () => document.querySelector('#salary-component-form-modal')?.offsetParent !== null
    JS, 10000);

    $page->script(<<<'JS'
        () => {
            const wire = Livewire.all().find((c) => c.name === 'employee.salary').$wire;
            wire.salaryComponentForm.name = 'Allowance';
            wire.salaryComponentForm.amount = 100;
            wire.salaryComponentForm.payment_interval = 'monthly';
            document.querySelector('#salary-component-form-modal [x-on\\:click*="$wire.save()"]').click();
        }
    JS);

    $gross = json_encode(Number::currency(2600));
    waitForCondition($page, <<<JS
        () => document.querySelector('#salary-component-form-modal')?.offsetParent === null
            && document.querySelector('[x-text="\$wire.costs.gross"]')?.textContent.trim() === {$gross}
    JS, 10000);

    $page->assertNoJavascriptErrors();

    expect(bccomp($employee->refresh()->base_salary, '2500', 2))->toBe(0)
        ->and($employee->salaryComponents()->where('name', 'Allowance')->exists())->toBeTrue();
});
