<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;
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
        DB::table('permissions')
            ->leftJoin('permissions as existing', function (JoinClause $join) use ($to): void {
                $join->on('existing.guard_name', '=', 'permissions.guard_name')
                    ->where('existing.name', $to);
            })
            ->where('permissions.name', $from)
            ->whereNull('existing.id')
            ->update(['permissions.name' => $to]);

        foreach ([
            'role_has_permissions' => ['role_id'],
            'model_has_permissions' => ['model_type', 'model_id'],
        ] as $table => $columns) {
            $select = DB::table($table, 'pivot')
                ->join('permissions as old', 'old.id', '=', 'pivot.permission_id')
                ->join('permissions as new', function (JoinClause $join): void {
                    $join->on('new.guard_name', '=', 'old.guard_name');
                })
                ->where('old.name', $from)
                ->where('new.name', $to)
                ->select([
                    'new.id',
                    ...array_map(fn (string $column): string => 'pivot.' . $column, $columns),
                ]);

            DB::table($table)
                ->insertOrIgnoreUsing(['permission_id', ...$columns], $select);
        }

        DB::table('permissions')
            ->where('name', $from)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
