<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->permissions = [
        'show' => Permission::findOrCreate('api.roles.user.{id}.get'),
        'index' => Permission::findOrCreate('api.roles.get'),
        'create' => Permission::findOrCreate('api.roles.post'),
        'update' => Permission::findOrCreate('api.roles.put'),
        'give' => Permission::findOrCreate('api.roles.give.put'),
        'revoke' => Permission::findOrCreate('api.roles.revoke.put'),
        'assignUsers' => Permission::findOrCreate('api.roles.users.assign.put'),
        'revokeUsers' => Permission::findOrCreate('api.roles.users.revoke.put'),
        'delete' => Permission::findOrCreate('api.roles.{id}.delete'),
        'test' => Permission::findOrCreate('api.test'),
    ];
});

test('assign role user', function (): void {
    $role = Role::create(['name' => Str::random()]);

    $roleUsers = [
        'id' => $role->id,
        'users' => [
            'id' => $this->user->id,
        ],
    ];

    $this->user->givePermissionTo($this->permissions['assignUsers']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/roles/users/assign', $roleUsers);
    $response->assertStatus(200);

    expect($this->user->fresh()->hasRole($role->name))->toBeTrue();
});

test('create role', function (): void {
    $role = [
        'name' => Str::random(),
        'guard_name' => $this->permissions['test']->guard_name,
        'permissions' => [
            'id' => $this->permissions['test']->id,
        ],
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/roles', $role);
    $response->assertStatus(201);

    $responseRole = json_decode($response->getContent())->data;

    expect($responseRole->name)->toEqual($role['name']);
    expect($responseRole->guard_name)->toEqual($role['guard_name']);
    expect($responseRole->permissions[0]->id)->toEqual($role['permissions']['id']);
});

test('create roles validation fails', function (): void {
    $role = [
        'guard_name' => $this->permissions['test']->guard_name,
        'permissions' => [
            'id' => $this->permissions['test']->id,
        ],
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/roles', $role);
    $response->assertStatus(422);
});

test('delete role', function (): void {
    $role = Role::create(['name' => Str::random()]);

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/roles/' . $role->id);
    $response->assertStatus(204);
});

test('delete role role not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/roles/' . Role::query()->max('id') + 100);
    $response->assertStatus(404);
});

test('delete role super admin', function (): void {
    $role = Role::findOrCreate('Super Admin');

    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->delete('/api/roles/' . $role->id);
    $response->assertStatus(423);
});

test('get roles', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/roles');
    $response->assertStatus(200);

    $roles = json_decode($response->getContent())->data->data;

    foreach ($roles as $role) {
        expect(Role::where('name', $role->name)->exists())->toBeTrue();
    }
});

test('get user roles', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/roles/user/' . $this->user->id);
    $response->assertStatus(200);

    $roles = json_decode($response->getContent())->data;

    foreach ($roles as $role) {
        expect($this->user->hasRole($role->name))->toBeTrue();
    }
});

test('get user roles user not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show'])->load('permissions');
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/roles/user/' . ++$this->user->id);
    $response->assertStatus(404);
});

test('give role permission', function (): void {
    $role = Role::create(['name' => Str::random()]);

    $rolePermissions = [
        'id' => $role->id,
        'permissions' => [
            'id' => $this->permissions['test']->id,
        ],
    ];

    $this->user->givePermissionTo($this->permissions['give']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/roles/give', $rolePermissions);
    $response->assertStatus(200);

    expect($role->hasPermissionTo($this->permissions['test']))->toBeTrue();
});

test('revoke role permission', function (): void {
    $role = Role::create(['name' => Str::random()]);

    $rolePermissions = [
        'id' => $role->id,
        'permissions' => [
            'id' => $this->permissions['test']->id,
        ],
    ];

    $this->user->givePermissionTo($this->permissions['revoke']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/roles/revoke', $rolePermissions);
    $response->assertStatus(200);

    expect($role->hasPermissionTo($this->permissions['test']))->toBeFalse();
});

test('revoke role permission validation fails', function (): void {
    $rolePermissions = [
        'id' => Str::random(),
        'permissions' => [
            'id' => $this->permissions['test']->id,
        ],
    ];

    $this->user->givePermissionTo($this->permissions['revoke']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/roles/revoke', $rolePermissions);
    $response->assertStatus(422);
});

test('revoke role user', function (): void {
    $role = Role::create(['name' => Str::random()]);

    $roleUsers = [
        'id' => $role->id,
        'users' => [
            'id' => $this->user->id,
        ],
    ];

    $this->user->givePermissionTo($this->permissions['revokeUsers']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/roles/users/revoke', $roleUsers);
    $response->assertStatus(200);

    expect($this->user->fresh()->hasRole($role->name))->toBeFalse();
});

test('revoke role user validation fails', function (): void {
    $roleUsers = [
        'id' => Str::random(),
        'users' => [
            'id' => $this->user->id,
        ],
    ];

    $this->user->givePermissionTo($this->permissions['revokeUsers']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/roles/users/revoke', $roleUsers);
    $response->assertStatus(422);
});
