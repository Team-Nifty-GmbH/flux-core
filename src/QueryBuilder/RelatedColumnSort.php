<?php

namespace FluxErp\QueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\Sorts\Sort;

class RelatedColumnSort implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        $direction = $descending ? 'DESC' : 'ASC';

        $exploded = explode('.', $property);
        $baseModel = app($exploded[0]);
        $method = $exploded[1];
        $select = $exploded[2];

        $relation = $baseModel->$method();
        $related = $relation->getRelated();
        $relatedTable = $related->getTable();

        $subQuery = DB::table($relatedTable)
            ->select($relatedTable . '.' . $select);

        if ($relation instanceof BelongsTo) {
            $subQuery->whereColumn($relation->getQualifiedForeignKeyName(), $relation->getQualifiedOwnerKeyName());
        } elseif ($relation instanceof HasOne) {
            $subQuery->whereColumn($relation->getQualifiedParentKeyName(), $relation->getQualifiedForeignKeyName());
        } elseif ($relation instanceof HasOneThrough) {
            $throughModel = $relation->getParent();
            $subQuery
                ->join(
                    $throughModel->getTable(),
                    $relation->getQualifiedForeignKeyName(), '=', $relation->getQualifiedParentKeyName()
                )
                ->whereColumn($relation->getQualifiedLocalKeyName(), $relation->getQualifiedFirstKeyName());
        } elseif ($relation instanceof MorphOne) {
            $subQuery
                ->where($relation->getQualifiedMorphType(), $relation->getMorphClass())
                ->whereColumn($relation->getQualifiedParentKeyName(), $relation->getQualifiedForeignKeyName());
        } elseif ($relation instanceof MorphToMany) {
            $subQuery
                ->join(
                    $relation->getTable(),
                    $relation->getQualifiedRelatedKeyName(), '=', $relation->getQualifiedRelatedPivotKeyName())
                ->whereColumn($relation->getQualifiedParentKeyName(), $relation->getQualifiedForeignPivotKeyName())
                ->where($relation->getTable() . '.' . $relation->getMorphType(), $relation->getMorphClass())
                ->orderBy($relatedTable . '.' . $select, $direction)
                ->limit(1);
        } else {
            return;
        }

        $subQuery->when(in_array(SoftDeletes::class, class_uses_recursive($related)), function ($query) {
            return $query->whereNull('deleted_at');
        });

        $query->orderBy($subQuery, $direction);
    }
}
