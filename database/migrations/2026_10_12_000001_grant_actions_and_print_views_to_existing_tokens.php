<?php

use FluxErp\Contracts\OffersPrinting;
use FluxErp\Facades\Action;
use FluxErp\Models\Token;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
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

        $permissionsTable = config('permission.table_names.permissions');

        DB::table($permissionsTable)->insertOrIgnore(
            array_map(
                fn (string $name): array => [
                    'name' => $name,
                    'guard_name' => 'token',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                $this->permissionNames()
            )
        );

        $pivotTable = config('permission.table_names.model_has_permissions');
        $pivotKey = config('permission.column_names.permission_pivot_key') ?? 'permission_id';
        $morphKey = config('permission.column_names.model_morph_key');

        foreach ($tokenIds as $tokenId) {
            DB::table($pivotTable)->insertUsing(
                [$pivotKey, 'model_type', $morphKey],
                DB::table($permissionsTable)
                    ->select('id')
                    ->selectRaw('?', [morph_alias(Token::class)])
                    ->selectRaw('?', [$tokenId])
                    ->where('guard_name', 'token')
                    ->where(fn (Builder $query): Builder => $query
                        ->where('name', 'like', 'action.%')
                        ->orWhere('name', 'like', 'print.%')
                    )
                    ->whereNotExists(fn (Builder $query): Builder => $query
                        ->from($pivotTable)
                        ->whereColumn($pivotTable . '.' . $pivotKey, $permissionsTable . '.id')
                        ->where($pivotTable . '.model_type', morph_alias(Token::class))
                        ->where($pivotTable . '.' . $morphKey, $tokenId)
                    )
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        //
    }

    protected function permissionNames(): array
    {
        $names = [];

        foreach (Action::all() as $action) {
            if ($action['class']::hasPermission()) {
                $names[] = 'action.' . $action['name'];
            }
        }

        foreach (Relation::morphMap() as $alias => $class) {
            $model = app($class);

            if ($model instanceof OffersPrinting) {
                foreach ($model->getAvailableViews() as $view) {
                    $names[] = print_view_to_permission($view, $alias);
                }
            }
        }

        return $names;
    }
};
