<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class() extends Migration
{
    public function up(): void
    {
        DB::table('role_has_permissions')
            ->insertOrIgnoreUsing(
                ['permission_id', 'role_id'],
                DB::table('roles')
                    ->join('permissions', function (JoinClause $join): void {
                        $join->on('permissions.guard_name', '=', 'roles.guard_name')
                            ->where('permissions.name', 'like', 'model.%.get');
                    })
                    ->select(['permissions.id', 'roles.id as role_id'])
            );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void {}
};
