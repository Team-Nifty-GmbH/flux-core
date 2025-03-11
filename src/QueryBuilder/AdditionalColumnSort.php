<?php

namespace FluxErp\QueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\Sorts\Sort;

class AdditionalColumnSort implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        $exploded = explode('.', $property);
        $className = $exploded[0];
        $table = app($className)->getTable();

        $direction = $descending ? 'DESC' : 'ASC';

        $subQuery = DB::table('additional_columns')
            ->select('meta.value')
            ->join('meta', 'additional_columns.id', '=', 'meta.additional_column_id')
            ->where('additional_columns.model_type', $className)
            ->where('additional_columns.name', $exploded[1])
            ->whereColumn($table . '.id', 'meta.model_id');

        $query->orderBy($subQuery, $direction);
    }
}
