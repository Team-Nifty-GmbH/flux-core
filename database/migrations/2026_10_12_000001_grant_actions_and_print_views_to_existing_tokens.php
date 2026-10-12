<?php

use FluxErp\Console\Commands\Init\InitPermissions;
use FluxErp\Models\Token;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Action and print view permissions exist for the token guard from now on.
 * Tokens created before keep every action and print view, tokens created
 * afterwards start with what they are granted.
 */
return new class() extends Migration
{
    public function up(): void
    {
        $tokenIds = DB::table('personal_access_tokens')
            ->where('tokenable_type', morph_alias(Token::class))
            ->get(['tokenable_id', 'abilities'])
            ->filter(fn (object $row): bool => array_intersect(
                json_decode($row->abilities, true) ?? [],
                ['*', 'user', 'interface']
            ) !== [])
            ->pluck('tokenable_id')
            ->unique();

        if ($tokenIds->isEmpty()) {
            return;
        }

        Artisan::call(InitPermissions::class);

        $permissionIds = DB::table('permissions')
            ->where('guard_name', 'token')
            ->where(fn (Builder $query): Builder => $query
                ->where('name', 'like', 'action.%')
                ->orWhere('name', 'like', 'print.%')
            )
            ->pluck('id');

        $tokenIds->crossJoin($permissionIds)
            ->map(fn (array $pair): array => [
                'permission_id' => $pair[1],
                'model_type' => morph_alias(Token::class),
                config('permission.column_names.model_morph_key') => $pair[0],
            ])
            ->chunk(1000)
            ->each(fn (Collection $rows): int => DB::table(config('permission.table_names.model_has_permissions'))
                ->insertOrIgnore($rows->values()->all())
            );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        //
    }
};
