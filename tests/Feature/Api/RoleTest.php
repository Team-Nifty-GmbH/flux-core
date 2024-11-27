<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class RoleTest extends BaseSetup
{
    use DatabaseTransactions;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_get_user_roles()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/roles/user/' . $this->user->id);
        $response->assertStatus(200);

        $roles = json_decode($response->getContent())->data;

        foreach ($roles as $role) {
            $this->assertTrue($this->user->hasRole($role->name));
        }
    }

    public function test_get_user_roles_user_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show'])->load('permissions');
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/roles/user/' . ++$this->user->id);
        $response->assertStatus(404);
    }

    public function test_get_roles()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/roles');
        $response->assertStatus(200);

        $roles = json_decode($response->getContent())->data->data;

        foreach ($roles as $role) {
            $this->assertTrue(Role::where('name', $role->name)->exists());
        }
    }

    public function test_create_role()
    {
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

        $this->assertEquals($role['name'], $responseRole->name);
        $this->assertEquals($role['guard_name'], $responseRole->guard_name);
        $this->assertEquals($role['permissions']['id'], $responseRole->permissions[0]->id);
    }

    public function test_create_roles_validation_fails()
    {
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
    }

    public function test_give_role_permission()
    {
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

        $this->assertTrue($role->hasPermissionTo($this->permissions['test']));
    }

    public function test_revoke_role_permission()
    {
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

        $this->assertFalse($role->hasPermissionTo($this->permissions['test']));
    }

    public function test_revoke_role_permission_validation_fails()
    {
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
    }

    public function test_assign_role_user()
    {
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

        $this->assertTrue($this->user->fresh()->hasRole($role->name));
    }

    public function test_revoke_role_user()
    {
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

        $this->assertFalse($this->user->fresh()->hasRole($role->name));
    }

    public function test_revoke_role_user_validation_fails()
    {
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
    }

    public function test_delete_role()
    {
        $role = Role::create(['name' => Str::random()]);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/roles/' . $role->id);
        $response->assertStatus(204);
    }

    public function test_delete_role_role_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/roles/' . Role::query()->max('id') + 100);
        $response->assertStatus(404);
    }

    public function test_delete_role_super_admin()
    {
        $role = Role::findOrCreate('Super Admin');

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/roles/' . $role->id);
        $response->assertStatus(423);
    }
}
