<?php

namespace FluxErp\QueryBuilder;

use FluxErp\Models\AdditionalColumn;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class AdditionalColumnFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        $exploded = explode('.', $property);

        if (! is_array($value)) {
            $value = [$value];
        }

        $className = $exploded[0];
        $table = app($className)->getTable();

        $exact = resolve_static(AdditionalColumn::class, 'query')
            ->where('model_type', $className)
            ->where('name', $exploded[1])
            ->whereNotNull('values')
            ->exists();

        $mhvAlias = 'mhv_'.strtolower($exploded[1]);
        $acAlias = 'ac_'.strtolower($exploded[1]);

        $query->join('model_has_values AS '.$mhvAlias, $table.'.id', '=', $mhvAlias.'.model_id')
            ->join('additional_columns AS '.$acAlias, $mhvAlias.'.additional_column_id', '=', $acAlias.'.id')
            ->where($acAlias.'.model_type', $className)
            ->where($acAlias.'.name', $exploded[1])
            ->when($exact, function ($query) use ($value, $mhvAlias) {
                $query->where(function ($query) use ($value, $mhvAlias) {
                    $query->where($mhvAlias.'.value', array_shift($value));

                    foreach ($value as $item) {
                        $query->orWhere($mhvAlias.'.value', $item);
                    }

                    return $query;
                });
            })
            ->when(! $exact, function ($query) use ($value, $mhvAlias) {
                $query->where(function ($query) use ($value, $mhvAlias) {
                    $query->where($mhvAlias.'.value', 'LIKE', '%'.array_shift($value).'%');

                    foreach ($value as $item) {
                        $query->orWhere($mhvAlias.'.value', 'LIKE', '%'.$item.'%');
                    }

                    return $query;
                });
            })
            ->select($table.'.*');
    }
}
