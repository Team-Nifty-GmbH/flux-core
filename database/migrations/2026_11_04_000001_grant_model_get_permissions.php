<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class() extends Migration
{
    public function up(): void
    {
        DB::table('permissions')
            ->insertOrIgnoreUsing(
                ['name', 'guard_name', 'created_at', 'updated_at'],
                DB::table('permissions')
                    ->where('guard_name', 'web')
                    ->where('name', 'like', 'model.%.get')
                    ->select(['name', DB::raw(DB::getPdo()->quote('token')), 'created_at', 'updated_at'])
            );

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

        $userType = 'user';

        DB::table('model_has_permissions')
            ->insertOrIgnoreUsing(
                ['permission_id', 'model_type', 'model_id'],
                DB::table('users')
                    ->join('permissions', function (JoinClause $join): void {
                        $join->where('permissions.guard_name', 'web')
                            ->where('permissions.name', 'like', 'model.%.get');
                    })
                    ->whereNotExists(function (Builder $query) use ($userType): void {
                        $query->from('model_has_roles')
                            ->whereColumn('model_has_roles.model_id', 'users.id')
                            ->where('model_has_roles.model_type', $userType);
                    })
                    ->whereNull('users.deleted_at')
                    ->select(['permissions.id', DB::raw(DB::getPdo()->quote($userType)), 'users.id as model_id'])
            );

        DB::table('model_has_permissions')
            ->insertOrIgnoreUsing(
                ['permission_id', 'model_type', 'model_id'],
                DB::table('model_has_permissions as search')
                    ->join('permissions as search_permission', 'search_permission.id', '=', 'search.permission_id')
                    ->join('permissions', function (JoinClause $join): void {
                        $join->where('permissions.guard_name', 'token')
                            ->where('permissions.name', 'like', 'model.%.get');
                    })
                    ->where('search_permission.name', 'api.search.{model}.get')
                    ->where('search_permission.guard_name', 'token')
                    ->where('search.model_type', 'token')
                    ->select(['permissions.id', 'search.model_type', 'search.model_id'])
            );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void {}
};
