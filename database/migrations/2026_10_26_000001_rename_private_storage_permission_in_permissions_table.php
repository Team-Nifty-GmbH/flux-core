<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class() extends Migration
{
    public function up(): void
    {
        $this->renamePermission('private-storage.{path}.get', 'user.private-storage.{path}.get');
    }

    public function down(): void
    {
        $this->renamePermission('user.private-storage.{path}.get', 'private-storage.{path}.get');
    }

    protected function renamePermission(string $from, string $to): void
    {
        foreach (DB::table('permissions')->where('name', $from)->get(['id', 'guard_name']) as $permission) {
            $targetId = DB::table('permissions')
                ->where('name', $to)
                ->where('guard_name', $permission->guard_name)
                ->value('id');

            if (is_null($targetId)) {
                DB::table('permissions')
                    ->where('id', $permission->id)
                    ->update(['name' => $to]);

                continue;
            }

            foreach (['role_has_permissions', 'model_has_permissions'] as $table) {
                DB::table($table)->insertOrIgnore(
                    DB::table($table)
                        ->where('permission_id', $permission->id)
                        ->get()
                        ->map(fn (object $row): array => array_merge((array) $row, ['permission_id' => $targetId]))
                        ->all()
                );
            }

            DB::table('permissions')
                ->where('id', $permission->id)
                ->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
