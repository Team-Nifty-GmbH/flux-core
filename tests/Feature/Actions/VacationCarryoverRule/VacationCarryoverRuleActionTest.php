<?php

use FluxErp\Actions\VacationCarryoverRule\CreateVacationCarryoverRule;
use FluxErp\Actions\VacationCarryoverRule\DeleteVacationCarryoverRule;
use FluxErp\Actions\VacationCarryoverRule\UpdateVacationCarryoverRule;

test('create vacation carryover rule', function (): void {
    $rule = CreateVacationCarryoverRule::make(['name' => 'Standard'])
        ->validate()->execute();

    expect($rule)->name->toBe('Standard');
});

test('create vacation carryover rule requires name', function (): void {
    CreateVacationCarryoverRule::assertValidationErrors([], 'name');
});

test('update vacation carryover rule', function (): void {
    $rule = CreateVacationCarryoverRule::make(['name' => 'Standard'])
        ->validate()->execute();

    $updated = UpdateVacationCarryoverRule::make([
        'id' => $rule->getKey(),
        'name' => 'Extended',
    ])->validate()->execute();

    expect($updated->name)->toBe('Extended');
});

test('delete vacation carryover rule', function (): void {
    $rule = CreateVacationCarryoverRule::make(['name' => 'Temp'])
        ->validate()->execute();

    expect(DeleteVacationCarryoverRule::make(['id' => $rule->getKey()])
        ->validate()->execute())->toBeTrue();
});
