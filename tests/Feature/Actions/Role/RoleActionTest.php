<?php

use FluxErp\Actions\Role\CreateRole;
use FluxErp\Actions\Role\DeleteRole;
use FluxErp\Actions\Role\UpdateRole;
use FluxErp\Models\Role;
use Illuminate\Validation\ValidationException;

test('create role', function (): void {
    $role = CreateRole::make([
        'name' => 'Editor',
        'guard_name' => 'web',
    ])->validate()->execute();

    expect($role)
        ->toBeInstanceOf(Role::class)
        ->name->toBe('Editor');
});

test('create role requires name', function (): void {
    CreateRole::assertValidationErrors([], 'name');
});

test('update role', function (): void {
    $role = Role::factory()->create(['guard_name' => 'web']);

    $updated = UpdateRole::make([
        'id' => $role->getKey(),
        'name' => 'Admin',
    ])->validate()->execute();

    expect($updated->name)->toBe('Admin');
});

test('delete role', function (): void {
    $role = Role::factory()->create(['guard_name' => 'web']);

    expect(DeleteRole::make(['id' => $role->getKey()])
        ->validate()->execute())->toBeTrue();
});

test('cannot delete super admin role', function (): void {
    $role = Role::factory()->create(['name' => 'Super Admin', 'guard_name' => 'web']);

    expect(fn () => DeleteRole::make(['id' => $role->getKey()])
        ->validate()->execute()
    )->toThrow(ValidationException::class);
});
