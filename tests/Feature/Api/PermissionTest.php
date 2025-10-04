<?php

use FluxErp\Models\Permission;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->permissions = [
        'show' => Permission::findOrCreate('api.permissions.user.{id}.get'),
        'index' => Permission::findOrCreate('api.permissions.get'),
        'create' => Permission::findOrCreate('api.permissions.post'),
        'update' => Permission::findOrCreate('api.permissions.put'),
        'give' => Permission::findOrCreate('api.permissions.give.put'),
        'revoke' => Permission::findOrCreate('api.permissions.revoke.put'),
        'delete' => Permission::findOrCreate('api.permissions.{id}.delete'),
    ];
});

test('create permission', function (): void {
    $permission = [
        'name' => Str::random(),
        'guard_name' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/permissions', $permission);
    $response->assertCreated();

    $responsePermission = json_decode($response->getContent())->data;
    $dbPermission = Permission::query()
        ->whereKey($responsePermission->id)
        ->first();

    expect($dbPermission->name)->toEqual($permission['name']);
    expect($dbPermission->guard_name)->toEqual($permission['guard_name']);
});

test('create permission validation fails', function (): void {
    $permission = [
        'guard_name' => Str::random(),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/permissions', $permission);
    $response->assertUnprocessable();
});

test('delete permission', function (): void {
    $permission = Permission::create(['name' => Str::random(), 'guard_name' => 'api']);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/permissions/' . $permission->id);
    $response->assertNoContent();

    expect(Permission::query()->whereKey($permission->id)->exists())->toBeFalse();
});

test('delete permission permission is locked', function (): void {
    $permission = Permission::create(['name' => Str::random(), 'guard_name' => 'api']);
    $permission = Permission::query()->whereKey($permission->id)->first();
    $permission->is_locked = true;
    $permission->save();

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/permissions/' . $permission->id);
    $response->assertStatus(423);
});

test('delete permission permission not found', function (): void {
    $permission = Permission::create(['name' => Str::random(), 'guard_name' => 'api']);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/permissions/' . ++$permission->id);
    $response->assertNotFound();
});

test('get permissions', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/permissions');
    $response->assertOk();

    $permissions = json_decode($response->getContent())->data;

    foreach ($permissions->data as $permission) {
        expect(collect($this->permissions)
            ->where('name', $permission->name)
            ->first()
            ?->name)->toEqual($permission->name);
    }
});

test('get user permissions', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/permissions/user/' . $this->user->id);
    $response->assertOk();

    $permissions = data_get(json_decode($response->getContent(), true), 'data');
    $permissions = Permission::query()
        ->whereKey(array_column($permissions, 'id'))
        ->get();

    foreach ($permissions as $permission) {
        expect($this->user->hasPermissionTo($permission))->toBeTrue();
    }
});

test('get user permissions user not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show'])->load('permissions');
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/permissions/user/' . ++$this->user->id);
    $response->assertNotFound();
});

test('give user permission', function (): void {
    $permissionInstance = Permission::create(['name' => Str::random(), 'guard_name' => 'sanctum']);
    $permission = [
        'user_id' => $this->user->id,
        'permissions' => [
            'id' => $permissionInstance->id,
        ],
    ];

    $this->user->givePermissionTo($this->permissions['give']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/permissions/give', $permission);
    $response->assertOk();

    $this->user = $this->user->fresh();

    expect($this->user->hasPermissionTo($permissionInstance->name))->toBeTrue();
});

test('revoke user permission', function (): void {
    $permissionInstance = Permission::create(['name' => Str::random(), 'guard_name' => 'sanctum']);
    $permission = [
        'user_id' => $this->user->id,
        'permissions' => [
            'id' => $permissionInstance->id,
        ],
    ];

    $this->user->givePermissionTo($this->permissions['revoke']);
    Sanctum::actingAs($this->user, ['user']);

    $this->user->givePermissionTo($this->permissions['revoke']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/permissions/revoke', $permission);
    $response->assertOk();

    $permissions = json_decode($response->getContent())->data;

    expect(collect($permissions)
        ->where('id', $permissionInstance->id)
        ->first())->toBeNull();
});

test('revoke user permission validation fails', function (): void {
    $permission = [
        'user_id' => Str::random(),
        'permissions' => [
            'id' => $this->permissions['revoke']->id,
        ],
    ];

    $this->user->givePermissionTo($this->permissions['revoke']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/permissions/revoke', $permission);
    $response->assertUnprocessable();
});
