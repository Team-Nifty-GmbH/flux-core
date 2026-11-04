<?php

use FluxErp\Models\User;
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

        $userType = morph_alias(User::class);

        DB::table('model_has_permissions')
            ->insertOrIgnoreUsing(
                ['permission_id', 'model_type', 'model_id'],
                DB::table('users')
                    ->join('permissions', function (JoinClause $join): void {
                        $join->where('permissions.guard_name', 'web')
                            ->where('permissions.name', 'like', 'model.%.get');
                    })
                    ->whereNotExists(function ($query) use ($userType): void {
                        $query->from('model_has_roles')
                            ->whereColumn('model_has_roles.model_id', 'users.id')
                            ->where('model_has_roles.model_type', $userType);
                    })
                    ->whereNull('users.deleted_at')
                    ->select(['permissions.id', DB::raw(DB::getPdo()->quote($userType)), 'users.id as model_id'])
            );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void {}
};
