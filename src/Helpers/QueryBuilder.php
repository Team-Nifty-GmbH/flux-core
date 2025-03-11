<?php

namespace FluxErp\Helpers;

use FluxErp\Models\AdditionalColumn;
use FluxErp\QueryBuilder\AdditionalColumnFilter;
use FluxErp\QueryBuilder\AdditionalColumnSort;
use FluxErp\QueryBuilder\RelatedColumnSort;
use FluxErp\Traits\Filterable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder as LaravelQueryBuilder;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class QueryBuilder
{
    public static function allowedScopeFilters(object $model, array $columnTypes): Collection
    {
        $columns = $model::getColumns();

        foreach ($columns as $key => $column) {
            if (str_contains($column->Type, 'tinyint') ||
                count(array_filter($columnTypes, function ($item) use ($column) {
                    return str_contains($column->Type, $item);
                })) === 0) {
                unset($columns[$key]);
            }
        }

        return $columns;
    }

    public static function filterModel(object $model, ?Request $request = null): LaravelQueryBuilder
    {
        $queryBuilder = LaravelQueryBuilder::for($model::query(), $request);

        if (! method_exists($model, 'relationships')) {
            return $queryBuilder;
        }

        $includes = array_diff(
            array_keys($model->relationships()),
            ['additionalColumns', 'relatedModel', 'relatedBy']
        );

        $modelName = get_class($model);

        $relatedAllowedFilters = [];
        $relatedAllowedSorts = [];
        foreach ($includes as $include) {
            $relatedModel = $model->$include()->getRelated();
            if (in_array(Filterable::class, class_uses_recursive($relatedModel))) {
                $relatedAllowedFilters = array_merge(
                    $relatedAllowedFilters,
                    self::calculateFilters($relatedModel, $include)
                );

                $relation = $model->$include();
                if (
                    $relation instanceof BelongsTo ||
                    $relation instanceof HasOne ||
                    $relation instanceof HasOneThrough ||
                    $relation instanceof MorphOne ||
                    $relation instanceof MorphToMany
                ) {
                    $relatedAllowedSorts = array_merge(
                        $relatedAllowedSorts,
                        self::calculateRelatedSorts($modelName, $relatedModel, $include)
                    );
                }
            }
        }

        $relatedAllowedFields = [];
        foreach (explode(',', $request->include ?: '') as $includedItem) {
            if (! in_array($includedItem = trim($includedItem), $includes)) {
                continue;
            }

            $related = $model->$includedItem()->getRelated();

            $relatedAllowedFields = array_merge(
                $relatedAllowedFields,
                ModelInfo::forModel($related)->attributes
                    ->where('hidden', false)
                    ->where('virtual', false)
                    ->pluck('name')
                    ->map(fn ($value) => $includedItem . '.' . $value)
                    ->flip()
                    ->toArray()
            );
        }

        $allowed = $model::getColumns();
        $allowedFields = array_flip(
            array_map(
                fn ($item) => $model->getTable() . '.' . $item,
                $allowed->pluck('Field')->toArray()
            )
        );
        $queryBuilder->allowedFields(array_keys(array_merge($allowedFields, $relatedAllowedFields)));

        if (count($includes) > 0) {
            $queryBuilder->allowedIncludes($includes);
        }

        $modelFilters = self::calculateFilters($model);

        $additionalColumns = resolve_static(AdditionalColumn::class, 'query')
            ->where('model_type', $modelName)
            ->get()
            ->pluck('name')
            ->toArray();

        $additionalColumnsFilters = [];
        $additionalColumnsSorts = [];
        foreach ($additionalColumns as $additionalColumn) {
            $alias = $modelName . '.' . $additionalColumn;
            $additionalColumnsFilters[] = AllowedFilter::custom(
                $additionalColumn, app(AdditionalColumnFilter::class), $alias
            );

            $additionalColumnsSorts[] = AllowedSort::custom(
                $additionalColumn, app(AdditionalColumnSort::class), $alias
            );
        }

        $filters = array_merge($modelFilters, $additionalColumnsFilters, $relatedAllowedFilters);
        if (count($filters) > 0) {
            $queryBuilder->allowedFilters($filters);
        }

        $sorts = array_merge($allowed->pluck('Field')->toArray(), $additionalColumnsSorts, $relatedAllowedSorts);
        if (count($sorts) > 0) {
            $queryBuilder->allowedSorts($sorts);
        }

        return $queryBuilder;
    }

    private static function allowedFilters(array $filters, string $type): array
    {
        if (count($filters) < 1) {
            return [];
        }

        $result = [];
        foreach ($filters as $filter) {
            $result[] = AllowedFilter::{$type}($filter);
        }

        return $result;
    }

    private static function calculateFilters(object $model, ?string $related = null): array
    {
        $exactAndScopeColumnTypes = [
            'int',
            'time',
            'date',
            'decimal',
            'double',
        ];

        $partialColumnTypes = [
            'char',
            'text',
        ];

        $allowed = $model::getColumns();

        $exact = [];
        $partial = [];
        $scope = [];
        foreach ($allowed as $column) {
            if (str_contains($column->Type, 'tinyint')) {
                $exact[] = $related ? $related . '.' . $column->Field : $column->Field;

                continue;
            }

            if (count(array_filter($exactAndScopeColumnTypes, function ($item) use ($column) {
                return str_contains($column->Type, $item);
            })) > 0) {
                $exact[] = $related ? $related . '.' . $column->Field : $column->Field;
                $scope[] = $related ? $related . '.' . $column->Field : $column->Field;

                continue;
            }

            if (count(array_filter($partialColumnTypes, function ($item) use ($column) {
                return str_contains($column->Type, $item);
            })) > 0) {
                $partial[] = $related ? $related . '.' . $column->Field : $column->Field;
            }
        }

        $exactFilters = self::allowedFilters($exact, 'exact');
        $partialFilters = self::allowedFilters($partial, 'partial');
        $scopeFilters = [];

        if (count($scope) > 0) {
            $scopeFilters = self::allowedFilters($related ?
                [$related . '.scope', $related . '.between'] : ['scope', 'between'], 'scope'
            );
        }

        return array_merge($exactFilters, $partialFilters, $scopeFilters);
    }

    private static function calculateRelatedSorts(string $baseModelClass, object $model, string $relation): array
    {
        $allowed = $model::getColumns()->pluck('Field')->toArray();

        array_walk($allowed, function (&$item) use ($baseModelClass, $relation): void {
            $item = AllowedSort::custom(
                $relation . '.' . $item,
                new RelatedColumnSort(),
                implode('.', [$baseModelClass, $relation, $item])
            );
        });

        return $allowed;
    }
}
