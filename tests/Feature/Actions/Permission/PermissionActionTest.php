<?php

use FluxErp\Actions\Permission\CreatePermission;
use FluxErp\Actions\Permission\DeletePermission;
use FluxErp\Models\Permission;
use Illuminate\Validation\ValidationException;

test('create permission', function (): void {
    $permission = CreatePermission::make([
        'name' => 'edit-posts',
        'guard_name' => 'web',
    ])->validate()->execute();

    expect($permission)->name->toBe('edit-posts');
});

test('create permission requires name', function (): void {
    CreatePermission::assertValidationErrors([], 'name');
});

test('delete permission', function (): void {
    $permission = CreatePermission::make([
        'name' => 'temp-perm',
        'guard_name' => 'web',
    ])->validate()->execute();

    expect(DeletePermission::make(['id' => $permission->getKey()])
        ->validate()->execute())->toBeTrue();
});

test('cannot delete locked permission', function (): void {
    $permission = Permission::query()->create([
        'name' => 'locked-perm',
        'guard_name' => 'web',
        'is_locked' => true,
    ]);

    expect(fn () => DeletePermission::make(['id' => $permission->getKey()])
        ->validate()->execute()
    )->toThrow(ValidationException::class);
});
