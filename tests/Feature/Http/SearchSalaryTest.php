<?php

use FluxErp\Models\Employee;
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
