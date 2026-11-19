<?php

use FluxErp\Models\Category;
use FluxErp\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;
use TeamNiftyGmbH\DataTable\Helpers\RelationFinder;

test('every relation a merge moves points to columns that exist', function (): void {
    $broken = [];

    foreach (Relation::morphMap() as $alias => $class) {
        $model = new $class();

        if ($model instanceof Category) {
            $model->model_type = morph_alias(Product::class);
        }

        $relations = RelationFinder::forModel($model)
            ->filter(fn (object $relation) => in_array(
                $relation->type,
                [
                    BelongsToMany::class,
                    HasOne::class,
                    HasMany::class,
                    MorphOne::class,
                    MorphMany::class,
                    MorphToMany::class,
                ]
            ));

        foreach ($relations as $relationItem) {
            if (! method_exists($model, $relationItem->name)) {
                continue;
            }

            $relation = $model->{$relationItem->name}();

            $table = $relation instanceof BelongsToMany
                ? $relation->getTable()
                : $relation->getRelated()->getTable();
            $columns = $relation instanceof BelongsToMany
                ? array_merge(
                    [$relation->getForeignPivotKeyName(), $relation->getRelatedPivotKeyName()],
                    $relation->getPivotColumns()
                )
                : [$relation->getForeignKeyName()];

            $missing = array_diff($columns, Schema::getColumnListing($table));

            if ($missing) {
                $broken[] = $alias . '::' . $relationItem->name . ' -> ' . $table . '.' . implode(', ', $missing);
            }
        }
    }

    expect($broken)->toBe([]);
});
