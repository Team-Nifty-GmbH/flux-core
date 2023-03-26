<?php

namespace FluxErp\QueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\Sorts\Sort;

class AdditionalColumnSort implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property)
    {
        $exploded = explode('.', $property);
        $className = $exploded[0];
        $table = (new $className)->getTable();

        $direction = $descending ? 'DESC' : 'ASC';

        $subQuery = DB::table('additional_columns')
            ->select('model_has_values.value')
            ->join('model_has_values', 'additional_columns.id', '=', 'model_has_values.additional_column_id')
            ->where('additional_columns.model_type', $className)
            ->where('additional_columns.name', $exploded[1])
            ->whereColumn($table . '.id', 'model_has_values.model_id');

        $query->orderBy($subQuery, $direction);
    }
}
