<?php

use FluxErp\Actions\AbsencePolicy\CreateAbsencePolicy;
use FluxErp\Actions\AbsencePolicy\DeleteAbsencePolicy;
use FluxErp\Actions\AbsencePolicy\UpdateAbsencePolicy;

test('create absence policy', function (): void {
    $policy = CreateAbsencePolicy::make(['name' => 'Standard Policy'])
        ->validate()->execute();

    expect($policy)->name->toBe('Standard Policy');
});

test('create absence policy requires name', function (): void {
    CreateAbsencePolicy::assertValidationErrors([], 'name');
});

test('update absence policy', function (): void {
    $policy = CreateAbsencePolicy::make(['name' => 'Original'])
        ->validate()->execute();

    $updated = UpdateAbsencePolicy::make([
        'id' => $policy->getKey(),
        'name' => 'Updated Policy',
    ])->validate()->execute();

    expect($updated->name)->toBe('Updated Policy');
});

test('delete absence policy', function (): void {
    $policy = CreateAbsencePolicy::make(['name' => 'Temp'])
        ->validate()->execute();

    expect(DeleteAbsencePolicy::make(['id' => $policy->getKey()])
        ->validate()->execute())->toBeTrue();
});
