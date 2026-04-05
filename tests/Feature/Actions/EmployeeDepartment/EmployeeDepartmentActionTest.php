<?php

use FluxErp\Actions\EmployeeDepartment\CreateEmployeeDepartment;
use FluxErp\Actions\EmployeeDepartment\DeleteEmployeeDepartment;
use FluxErp\Actions\EmployeeDepartment\UpdateEmployeeDepartment;

test('create employee department', function (): void {
    $dept = CreateEmployeeDepartment::make(['name' => 'Engineering'])
        ->validate()->execute();

    expect($dept)->name->toBe('Engineering');
});

test('create employee department requires name', function (): void {
    CreateEmployeeDepartment::assertValidationErrors([], 'name');
});

test('update employee department', function (): void {
    $dept = CreateEmployeeDepartment::make(['name' => 'Engineering'])
        ->validate()->execute();

    $updated = UpdateEmployeeDepartment::make([
        'id' => $dept->getKey(),
        'name' => 'Product',
    ])->validate()->execute();

    expect($updated->name)->toBe('Product');
});

test('delete employee department', function (): void {
    $dept = CreateEmployeeDepartment::make(['name' => 'Temp Dept'])
        ->validate()->execute();

    expect(DeleteEmployeeDepartment::make(['id' => $dept->getKey()])
        ->validate()->execute())->toBeTrue();
});
