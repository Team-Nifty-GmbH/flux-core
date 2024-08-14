<?php

namespace FluxErp\Traits;

use FluxErp\Exceptions\InvalidFilter;
use FluxErp\Helpers\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use ReflectionMethod;

trait Filterable
{
    /**
     * Available relationships for the model.
     */
    protected static array $availableRelations = [];

    /**
     * Gets list of available relations for this model
     * And stores it in the variable for future use
     */
    public static function relationships(): array
    {
        return static::$availableRelations[static::class] ?? static::setAvailableRelations(
            array_reduce(
                (new ReflectionClass(static::class))->getMethods(ReflectionMethod::IS_PUBLIC),
                function ($result, ReflectionMethod $method) {
                    // If this function has a return type
                    ($returnType = (string) $method->getReturnType()) &&

                    // And this function returns a relation
                    is_subclass_of($returnType, Relation::class) &&

                    // Remove morph relations
                    strcmp($returnType, MorphTo::class) !== 0 &&

                    // Add name of this method to the relations array
                    ($result = array_merge($result, [$method->getName() => $returnType]));

                    return $result;
                }, []
            )
        );
    }

    /**
     * Stores relationships for future use
     */
    public static function setAvailableRelations(array $relations): array
    {
        static::$availableRelations[static::class] = $relations;

        return $relations;
    }

    public static function getColumns(bool $showHidden = false): Collection
    {
        $collection = collect(DB::select('DESCRIBE `'.(app(static::class))->getTable().'`;'));

        return $showHidden ? $collection : $collection->whereNotIn('Field', (app(static::class))->getHidden());
    }

    public function scopeScope(Builder $query, $item): Builder
    {
        $disableException = config('query-builder.disable_invalid_filter_query_exception');
        $operators = ['<', '>', '<>', '><', '!'];
        $scopeColumnTypes = [
            'int',
            'time',
            'date',
            'decimal',
            'double',
        ];
        $exploded = explode('|', $item);

        if (count($exploded) !== 3) {
            if ($disableException) {
                return $query;
            } else {
                throw InvalidFilter::invalidFilterScheme($item, 'scope');
            }
        }

        $allowedColumns = QueryBuilder::allowedScopeFilters($this, $scopeColumnTypes);
        $column = $allowedColumns->where('Field', $exploded[0])->first();
        if (! $column) {
            if ($disableException) {
                return $query;
            } else {
                throw InvalidFilter::filterNotAllowed($exploded[0], $allowedColumns->pluck('Field'));
            }
        }

        if (! in_array($exploded[1], $operators)) {
            if ($disableException) {
                return $query;
            } else {
                throw InvalidFilter::operatorNotAllowed($exploded[1], $operators);
            }
        }

        $operator = match ($exploded[1]) {
            '<>' => '<=',
            '><' => '>=',
            '!' => '!=',
            default => $exploded[1],
        };

        return $query->where($column->Field, $operator, $exploded[2]);
    }

    public function scopeBetween(Builder $query, $item): Builder
    {
        $disableException = config('query-builder.disable_invalid_filter_query_exception');
        $scopeColumnTypes = [
            'int',
            'time',
            'date',
            'decimal',
            'double',
        ];
        $exploded = explode('|', $item);

        if (count($exploded) !== 2) {
            if ($disableException) {
                return $query;
            } else {
                throw InvalidFilter::invalidFilterScheme($item, 'between');
            }
        }

        $allowedColumns = QueryBuilder::allowedScopeFilters($this, $scopeColumnTypes);
        $column = $allowedColumns->where('Field', $exploded[0])->first();
        if (! $column) {
            if ($disableException) {
                return $query;
            } else {
                throw InvalidFilter::filterNotAllowed($exploded[0], $allowedColumns);
            }
        }

        $values = explode(';', $exploded[1]);
        if (count($values) !== 2) {
            if ($disableException) {
                return $query;
            } else {
                throw InvalidFilter::invalidFilterScheme($item, 'between');
            }
        }

        return $query->whereBetween($column->Field, $values);
    }
}
