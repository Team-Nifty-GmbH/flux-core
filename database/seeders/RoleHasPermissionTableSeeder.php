<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Permission;
use FluxErp\Models\Role;
use Illuminate\Database\Seeder;

class RoleHasPermissionTableSeeder extends Seeder
{
    public function run(): void
    {
        $roleIds = Role::query()->get('id');
        $cutRoleIds = $roleIds->random(bcfloor($roleIds->count() * 0.6));
        $permissionIds = Permission::query()->get('id');
        $cutPermissionIds = $permissionIds->random(bcfloor($permissionIds->count() * 0.6));

        foreach ($cutRoleIds as $cutRoleId) {
            $cutRoleId->permissions()->attach($cutPermissionIds->random(
                rand(1, bcfloor($cutPermissionIds->count() / 2))
            ));
        }
    }
}
