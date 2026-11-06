<?php

use FluxErp\Enums\PaymentIntervalEnum;
use FluxErp\Models\Activity;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\SalaryComponent;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
        'base_salary' => 2000,
    ]);
});

test('salary components are not searchable', function (): void {
    $this->post(route('search', SalaryComponent::class), [
        'search' => '',
        'searchFields' => ['name'],
    ])
        ->assertForbidden();
});

test('employees cannot be searched by hidden columns', function (array $payload): void {
    $this->post(route('search', Employee::class), array_merge(['search' => '', 'searchFields' => ['name']], $payload))
        ->assertForbidden();
})
    ->with([
        'search field' => [['searchFields' => ['base_salary'], 'search' => '2000']],
        'order by' => [['orderBy' => 'base_salary']],
        'where list' => [['where' => [['base_salary', '>', 1999]]]],
        'where single' => [['where' => ['base_salary', '>', 1999]]],
        'where assoc' => [['where' => ['base_salary' => 2000]]],
        'where table prefix' => [['where' => [['employees.base_salary', '>', 1999]]]],
        'where database prefix' => fn (): array => [
            'where' => [[DB::connection()->getDatabaseName() . '.employees.base_salary', '>', 1999]],
        ],
        'where string keys' => [['where' => [['a' => 'base_salary', 'b' => '>', 'c' => 1999]]]],
        'where upper case' => [['where' => [['BASE_SALARY', '>', 1999]]]],
        'where in' => [['whereIn' => [['hourly_rate', [20]]]]],
        'where between' => [['whereBetween' => ['employer_contribution_rate', [1, 100]]]],
        'where between named' => [['whereBetween' => ['column' => 'base_salary', 'values' => [1999, 2001]]]],
        'where relation' => [['whereRelation' => ['supervisor', 'base_salary', '>', 1999]]],
        'nested relation field' => [['searchFields' => ['supervisor.base_salary'], 'search' => '2000']],
        'where has hidden relation' => [['whereHas' => 'salaryComponents']],
        'option value' => [['selected' => [2000], 'option-value' => 'base_salary', 'search' => null]],
    ]);

test('employees stay searchable by visible columns', function (): void {
    $this->post(route('search', Employee::class), [
        'search' => 'Test',
        'searchFields' => ['name'],
        'where' => [['is_active', '=', true]],
        'orderBy' => 'name',
    ])
        ->assertOk()
        ->assertJsonFragment(['id' => $this->employee->getKey()]);
});

test('employees do not return salary data as requested fields', function (array $payload): void {
    app(SalaryComponent::class)->create([
        'employee_id' => $this->employee->getKey(),
        'name' => 'Allowance',
        'amount' => 4321,
        'payment_interval' => PaymentIntervalEnum::Monthly,
    ]);

    $response = $this->post(
        route('search', Employee::class),
        array_merge(['search' => 'Test', 'searchFields' => ['name']], $payload)
    )
        ->assertOk();

    expect($response->json('0.id'))->toBe($this->employee->getKey())
        ->and($response->getContent())
        ->not->toContain('4321')
        ->not->toContain('2000');
})
    ->with([
        'fields' => [['fields' => ['salaryComponents']]],
        'fields lower case' => [['fields' => ['salarycomponents']]],
        'appends lower case' => [['appends' => ['salarycomponents']]],
        'fields hidden column' => [['fields' => ['Base_Salary']]],
    ]);

test('employees cannot eager load salary components', function (array $payload): void {
    $this->post(route('search', Employee::class), array_merge(['search' => 'Test', 'searchFields' => ['name']], $payload))
        ->assertForbidden();
})
    ->with([
        'with' => [['with' => ['salarycomponents']]],
        'with columns' => [['with' => ['SalaryComponents:id,amount']]],
    ]);

test('departments cannot reach salary data through employees', function (array $payload): void {
    $this->post(route('search', EmployeeDepartment::class), array_merge(['search' => '', 'searchFields' => ['name']], $payload))
        ->assertForbidden();
})
    ->with([
        'with nested relation' => [['with' => ['employees.salarycomponents']]],
        'where relation array' => [['whereRelation' => ['employees', ['base_salary' => 2000]]]],
        'where doesnt have relation array' => [['whereDoesntHaveRelation' => ['employees', ['base_salary' => 2000]]]],
        'where relation named array' => [['whereRelation' => ['relation' => 'employees', 'column' => ['base_salary' => 2000]]]],
    ]);

test('activities cannot be filtered by logged changes', function (array $payload): void {
    $this->post(route('search', Activity::class), array_merge(['search' => '', 'searchFields' => ['log_name']], $payload))
        ->assertForbidden();
})
    ->with([
        'where json path' => [['where' => [['attribute_changes->attributes->base_salary', '>', 1999]]]],
        'where legacy json path' => [['where' => [['properties->attributes->base_salary', '>', 1999]]]],
        'search field' => [['searchFields' => ['attribute_changes'], 'search' => 'base_salary']],
        'search legacy field' => [['searchFields' => ['properties'], 'search' => 'base_salary']],
    ]);

test('logged salary changes of older rows are not returned', function (): void {
    $logged = ['attributes' => ['base_salary' => 9000, 'job_title' => 'Developer'], 'old' => ['base_salary' => 2000]];

    DB::table('activity_log')->insert([
        [
            'log_name' => 'model_events',
            'description' => 'updated',
            'subject_type' => $this->employee->getMorphClass(),
            'subject_id' => $this->employee->getKey(),
            'event' => 'updated',
            'attribute_changes' => json_encode($logged),
            'properties' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'log_name' => 'model_events',
            'description' => 'updated',
            'subject_type' => $this->employee->getMorphClass(),
            'subject_id' => $this->employee->getKey(),
            'event' => 'updated',
            'attribute_changes' => null,
            'properties' => json_encode($logged),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $activities = $this->post(route('search', Activity::class), [
        'search' => '',
        'searchFields' => ['log_name'],
        'where' => [['subject_id', '=', $this->employee->getKey()]],
        'limit' => 50,
    ])
        ->assertOk()
        ->getContent();

    $employee = $this->post(route('search', Employee::class), [
        'search' => 'Test',
        'searchFields' => ['name'],
        'fields' => ['activitiesAsSubject'],
    ])
        ->assertOk()
        ->getContent();

    expect($activities)->toContain('Developer')
        ->not->toContain('base_salary')
        ->and($employee)->toContain('Developer')
        ->not->toContain('base_salary');
});
