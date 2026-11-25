<?php

use FluxErp\Models\Employee;
use FluxErp\Models\VacationCarryoverRule;

test('employee resolves its vacation carryover rule', function (): void {
    $rule = VacationCarryoverRule::create(['name' => 'Standard']);

    $employee = app(Employee::class)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'user_id' => $this->user->getKey(),
        'firstname' => 'Test',
        'lastname' => 'Employee',
        'is_active' => true,
        'vacation_carryover_rule_id' => $rule->getKey(),
    ]);

    expect($employee->fresh()->vacationCarryOverRule)
        ->not->toBeNull()
        ->getKey()->toBe($rule->getKey());
});
