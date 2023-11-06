<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;

class PermissionTest extends BaseSetup
{
    use DatabaseTransactions;

    private array $permissions;

    public function setUp(): void
    {
        parent::setUp();

        $this->permissions = [
            'show' => Permission::findOrCreate('api.permissions.user.{id}.get'),
            'index' => Permission::findOrCreate('api.permissions.get'),
            'create' => Permission::findOrCreate('api.permissions.post'),
            'update' => Permission::findOrCreate('api.permissions.put'),
            'give' => Permission::findOrCreate('api.permissions.give.put'),
            'revoke' => Permission::findOrCreate('api.permissions.revoke.put'),
            'delete' => Permission::findOrCreate('api.permissions.{id}.delete'),
        ];
    }

    public function test_get_user_permissions()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/permissions/user/' . $this->user->id);
        $response->assertStatus(200);

        $permissions = json_decode($response->getContent())->data;

        foreach ($permissions as $permission) {
            $this->assertTrue($this->user->hasPermissionTo($permission->name));
        }
    }

    public function test_get_user_permissions_user_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show'])->load('permissions');
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/permissions/user/' . ++$this->user->id);
        $response->assertStatus(404);
    }

    public function test_get_permissions()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/permissions');
        $response->assertStatus(200);

        $permissions = json_decode($response->getContent())->data;

        foreach ($permissions->data as $permission) {
            $this->assertEquals($permission->name, collect($this->permissions)
                ->where('name', $permission->name)
                ->first()
                ->name
            );
        }
    }

    public function test_create_permission()
    {
        $permission = [
            'name' => Str::random(),
            'guard_name' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/permissions', $permission);
        $response->assertStatus(201);

        $responsePermission = json_decode($response->getContent())->data;
        $dbPermission = Permission::query()
            ->whereKey($responsePermission->id)
            ->first();

        $this->assertEquals($permission['name'], $dbPermission->name);
        $this->assertEquals($permission['guard_name'], $dbPermission->guard_name);
    }

    public function test_create_permission_validation_fails()
    {
        $permission = [
            'guard_name' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/permissions', $permission);
        $response->assertStatus(422);
    }

    public function test_give_user_permission()
    {
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
        $response->assertStatus(200);

        $this->user = $this->user->fresh();

        $this->assertTrue($this->user->hasPermissionTo($permissionInstance->name));
    }

    public function test_revoke_user_permission()
    {
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
        $response->assertStatus(200);

        $permissions = json_decode($response->getContent())->data;

        $this->assertNull(collect($permissions)
            ->where('id', $permissionInstance->id)
            ->first());
    }

    public function test_revoke_user_permission_validation_fails()
    {
        $permission = [
            'user_id' => Str::random(),
            'permissions' => [
                'id' => $this->permissions['revoke']->id,
            ],
        ];

        $this->user->givePermissionTo($this->permissions['revoke']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/permissions/revoke', $permission);
        $response->assertStatus(422);
    }

    public function test_delete_permission()
    {
        $permission = Permission::create(['name' => Str::random(), 'guard_name' => 'api']);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/permissions/' . $permission->id);
        $response->assertStatus(204);

        $this->assertFalse(Permission::query()->whereKey($permission->id)->exists());
    }

    public function test_delete_permission_permission_not_found()
    {
        $permission = Permission::create(['name' => Str::random(), 'guard_name' => 'api']);

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/permissions/' . ++$permission->id);
        $response->assertStatus(404);
    }

    public function test_delete_permission_permission_is_locked()
    {
        $permission = Permission::create(['name' => Str::random(), 'guard_name' => 'api']);
        $permission = Permission::query()->whereKey($permission->id)->first();
        $permission->is_locked = true;
        $permission->save();

        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/permissions/' . $permission->id);
        $response->assertStatus(423);
    }
}
