<?php

namespace FluxErp\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Laravel\Scout\SearchableScope;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Spatie\EloquentSortable\Sortable;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class SearchController extends Controller
{
    public function __invoke(Request $request, ?string $model = null)
    {
        // check if $model is a morph alias
        $model = morphed_model($model) ?? $model;
        $model = qualify_model(str_replace('/', '\\', $model));

        abort_unless(class_exists($model) && is_a($model, Model::class, true), 404);

        $this->guardRequest(app($model), $request);

        $isSearchable = in_array(
            Searchable::class,
            class_uses_recursive(resolve_static($model, 'class'))
        );

        abort_if(! $isSearchable && ! $request->input('searchFields'), 404);

        Event::dispatch('tall-datatables-searching', $request);

        if (! blank($request->input('selected')) && blank($request->input('search'))) {
            $selected = Arr::wrap($request->input('selected'));
            $optionValue = $request->input('option-value') ?: (app($model))->getKeyName();

            $query = resolve_static($model, 'query')
                ->withoutGlobalScopes([SoftDeletingScope::class])
                ->whereIn($optionValue, $selected);

            $constrained = $query->clone();
            $this->applyRequestConstraints($constrained, $request, $model);
            $result = $constrained->get();

            $missing = array_diff($selected, $result->pluck($optionValue)->all());

            if ($missing) {
                $result = $result->concat($query->whereIn($optionValue, $missing)->get());
            }

            return $this->formatAndDispatch($result, $model, $request);
        } elseif ($request->has('search') && $isSearchable && ! $request->input('searchFields')) {
            /** @var Builder $perPageSearch */
            $perPageSearch = count(Arr::except(
                app($model)->getGlobalScopes(),
                [
                    SoftDeletingScope::class,
                    SearchableScope::class,
                ]
            )) === 0 ? 20 : 1000;

            $query = ! is_string($request->input('search'))
                ? resolve_static($model, 'query')->limit(20)
                : resolve_static($model, 'search', ['query' => $request->input('search')])
                    ->toEloquentBuilder(highlight: [], perPage: $perPageSearch);
        } elseif ($request->has('search')) {
            $query = resolve_static($model, 'query');
            $query->where(function (Builder $query) use ($request): void {
                foreach (Arr::wrap($request->input('searchFields')) as $field) {
                    str_contains($field, '.')
                        ? $query->orWhereRelation(
                            Str::beforeLast($field, '.'),
                            Str::afterLast($field, '.'),
                            'like',
                            '%' . $request->input('search') . '%'
                        )
                        : $query->orWhere($field, 'like', '%' . $request->input('search') . '%');
                }
            });
        } else {
            /** @var Builder $query */
            $query = resolve_static($model, 'query');
        }

        if ($request->has('with')) {
            $query->with(
                array_map(
                    fn (mixed $relation): mixed => is_string($relation) && str_contains($relation, ':')
                        ? Str::before($relation, ':') . ':'
                            . implode(',', $this->plainColumns(explode(',', Str::after($relation, ':'))))
                        : $relation,
                    Arr::wrap($request->input('with'))
                )
            );
        }

        if ($request->has('limit')) {
            $query->limit($request->input('limit'));
        } else {
            $query->limit(10);
        }

        if ($request->has('orderBy')) {
            $query->orderBy($request->input('orderBy'), $request->input('orderDirection', 'asc'));
        }

        $this->applyRequestConstraints($query, $request, $model);

        $result = $query
            ->when(
                is_a(resolve_static($model, 'class'), Sortable::class, true),
                fn (Builder $query): Builder => $query->ordered()
            )
            ->latest()
            ->get();

        if ($request->has('appends')) {
            $result->each(function ($item) use ($request): void {
                $item->append(array_intersect($item->getAppends(), Arr::wrap($request->input('appends'))));
            });
        }

        return $this->formatAndDispatch($result, $model, $request);
    }

    protected function applyRequestConstraints(Builder $query, Request $request, string $model): void
    {
        if ($request->has('where')) {
            $query->where($request->input('where'));
        }

        if ($request->has('whereIn')) {
            foreach ($request->input('whereIn') as $whereIn) {
                $whereIn[1] = Arr::wrap($whereIn[1]);
                $query->whereIn(...$whereIn);
            }
        }

        if ($request->has('whereNotIn')) {
            foreach ($request->input('whereNotIn') as $whereNotIn) {
                $query->whereNotIn(...$whereNotIn);
            }
        }

        if ($request->has('whereNull')) {
            $query->whereNull(...$request->input('whereNull'));
        }

        if ($request->has('whereNotNull')) {
            $query->whereNotNull(...$request->input('whereNotNull'));
        }

        if ($request->has('whereBetween')) {
            $query->whereBetween(...$request->input('whereBetween'));
        }

        if ($request->has('whereNotBetween')) {
            $query->whereNotBetween(...$request->input('whereNotBetween'));
        }

        if ($request->has('whereDate')) {
            $query->whereDate(...$request->input('whereDate'));
        }

        if ($request->has('whereMonth')) {
            $query->whereMonth(...$request->input('whereMonth'));
        }

        if ($request->has('whereDay')) {
            $query->whereDay(...$request->input('whereDay'));
        }

        if ($request->has('whereYear')) {
            $query->whereYear(...$request->input('whereYear'));
        }

        if ($request->has('whereTime')) {
            $query->whereTime(...$request->input('whereTime'));
        }

        if ($request->has('select')) {
            $query->select($this->plainColumns(Arr::wrap($request->input('select'))));
        }

        if ($request->has('whereDoesntHave')) {
            $whereDoesntHave = $request->input('whereDoesntHave');
            if (is_array($whereDoesntHave) && array_is_list($whereDoesntHave)) {
                foreach ($whereDoesntHave as $relation) {
                    $query->whereDoesntHave($relation);
                }
            } else {
                $query->whereDoesntHave($whereDoesntHave);
            }
        }

        if ($request->has('whereHas')) {
            $whereHas = $request->input('whereHas');
            if (is_array($whereHas) && array_is_list($whereHas)) {
                foreach ($whereHas as $relation) {
                    $query->whereHas($relation);
                }
            } else {
                $query->whereHas($whereHas);
            }
        }

        if ($request->has('whereRelation')) {
            $query->whereRelation(...$request->input('whereRelation'));
        }

        if ($request->has('whereDoesntHaveRelation')) {
            $query->whereDoesntHaveRelation(...$request->input('whereDoesntHaveRelation'));
        }

        if ($request->has('doesntHave')) {
            $query->doesntHave(...$request->input('doesntHave'));
        }

        // Add local scopes to query
        $scopes = $request->input('scopes');
        if (is_array($scopes) && $scopes) {
            /** @var Model $modelInstance */
            $modelInstance = app($model);
            foreach ($scopes as $scope => $params) {
                if ($modelInstance->hasNamedScope($scope)) {
                    if (is_array($params)) {
                        $query->{$scope}(...$params);
                    } else {
                        $query->{$scope}($params);
                    }
                }
            }
        }
    }

    protected function columnsOf(mixed $where): array
    {
        if (! is_array($where)) {
            return [$where];
        }

        $columns = [];
        foreach ($where as $key => $value) {
            abort_if(is_numeric($key) && is_array($value) && count($value) > 3, 422);

            $columns = array_merge(
                $columns,
                is_numeric($key) && is_array($value) ? $this->columnsOf(Arr::first($value)) : [$key]
            );
        }

        return $columns;
    }

    protected function formatAndDispatch(Collection $result, string $model, Request $request)
    {
        if (is_a(app($model), InteractsWithDataTables::class)) {
            $result = $result->map(function ($item) use ($request): array {
                $formatted = array_merge(
                    [
                        'id' => $item->getKey(),
                        'label' => $item->getLabel() ?? '-',
                        'description' => $item->getDescription(),
                        'image' => $item->getAvatarUrl(),
                    ],
                    $this->visibleOnly($item, Arr::wrap($request->input('fields', []))),
                    $this->visibleOnly($item, Arr::wrap($request->input('appends', []))),
                );

                $mapping = Arr::wrap($request->input('mapping', []));
                $avatarUrl = data_get($formatted, 'image');

                // mapping sources are limited to keys already exposed above
                foreach ($mapping as $target => $source) {
                    data_set($formatted, $target, data_get($formatted, $source));
                }

                $formatted = $this->toPlainText($formatted);

                // the avatar is a url, not display text, so it keeps its full value
                if (! array_key_exists('image', $mapping)) {
                    $formatted['image'] = $avatarUrl;
                }

                return $formatted;
            });
        }

        Event::dispatch('tall-datatables-searched', [$request, $result]);

        return $result;
    }

    protected function guardColumns(?Model $model, mixed $columns): void
    {
        abort_unless($model, 422);

        $hidden = array_map(strtolower(...), $model->getHidden());

        foreach ($this->columnsOf($columns) as $column) {
            if (is_string($column)) {
                $column = Str::afterLast(Str::before(Str::before(strtolower($column), ' as '), '->'), '.');

                abort_if(in_array(trim($column), $hidden, true), 403);
            }
        }
    }

    protected function guardRequest(Model $model, Request $request): void
    {
        foreach (Arr::wrap($request->input('searchFields')) as $field) {
            abort_unless(is_string($field), 422);

            $this->guardColumns(
                str_contains($field, '.') ? $this->relatedModel($model, Str::beforeLast($field, '.')) : $model,
                Str::afterLast($field, '.')
            );
        }

        foreach (['with', 'whereHas', 'whereDoesntHave'] as $parameter) {
            foreach ($this->listArguments($request->input($parameter), PHP_INT_MAX) as $relation) {
                $this->relatedModel(
                    $model,
                    $parameter === 'with' && is_string($relation) ? Str::before($relation, ':') : $relation
                );
            }
        }

        $spreads = [];
        foreach ([
            'whereNull' => 1,
            'whereNotNull' => 1,
            'whereBetween' => 2,
            'whereNotBetween' => 2,
            'whereDate' => 3,
            'whereMonth' => 3,
            'whereDay' => 3,
            'whereYear' => 3,
            'whereTime' => 3,
        ] as $parameter => $boolean) {
            if ($request->has($parameter)) {
                $spreads[] = [$request->input($parameter), $boolean];
            }
        }

        foreach (['whereIn', 'whereNotIn'] as $parameter) {
            foreach (Arr::wrap($request->input($parameter)) as $arguments) {
                $spreads[] = [$arguments, 2];
            }
        }

        foreach ($spreads as [$arguments, $boolean]) {
            $this->guardColumns($model, data_get($this->listArguments($arguments, $boolean + 1, $boolean), 0));
        }

        foreach (['whereRelation', 'whereDoesntHaveRelation'] as $parameter) {
            if ($request->has($parameter)) {
                $arguments = $this->listArguments($request->input($parameter), 4);
                $this->guardColumns($this->relatedModel($model, data_get($arguments, 0)), data_get($arguments, 1));
            }
        }

        if ($request->has('doesntHave')) {
            $this->relatedModel($model, data_get($this->listArguments($request->input('doesntHave'), 2, 1), 0));
        }

        foreach (['orderBy', 'option-value'] as $parameter) {
            abort_unless(is_null($request->input($parameter)) || is_string($request->input($parameter)), 422);
            $this->guardColumns($model, $request->input($parameter));
        }

        $this->guardColumns($model, $request->input('where'));
    }

    protected function isRelation(Model $model, string $name): bool
    {
        if ($model->relationResolver($model::class, $name)) {
            return true;
        }

        if (! method_exists($model, $name)) {
            return false;
        }

        $method = new ReflectionMethod($model, $name);
        $returnType = $method->getReturnType();

        return $method->isPublic()
            && ! $method->isStatic()
            && $returnType
            && collect($returnType instanceof ReflectionUnionType ? $returnType->getTypes() : [$returnType])
                ->every(fn (ReflectionType $type): bool => $type instanceof ReflectionNamedType
                    && is_a($type->getName(), Relation::class, true)
                );
    }

    protected function listArguments(mixed $arguments, int $max, ?int $boolean = null): array
    {
        $arguments = Arr::wrap($arguments);

        abort_unless(array_is_list($arguments) && count($arguments) <= $max, 422);

        if (! is_null($boolean) && array_key_exists($boolean, $arguments)) {
            abort_unless(
                is_string($arguments[$boolean]) && in_array(strtolower($arguments[$boolean]), ['and', 'or'], true),
                422
            );
        }

        return $arguments;
    }

    // an alias or another spelling would rename a hidden column past the model's hidden list
    protected function plainColumns(array $columns): array
    {
        return array_values(preg_grep('/^([a-z0-9_]+\.)?([a-z0-9_]+|\*)$/', array_filter($columns, 'is_string')));
    }

    protected function visibleOnly(Model $item, array $keys): array
    {
        $keys = array_diff(array_filter($keys, 'is_string'), $item->getHidden());

        foreach ($keys as $key) {
            abort_if(
                method_exists($item, $key) && ! $item->hasAttribute($key) && ! $this->isRelation($item, $key),
                422
            );
        }

        return $item->only($item->getVisible() ? array_intersect($keys, $item->getVisible()) : $keys);
    }

    protected function relatedModel(Model $model, mixed $path): ?Model
    {
        abort_unless(is_string($path), 422);

        foreach (explode('.', $path) as $name) {
            abort_unless($model && $this->isRelation($model, $name), 422);

            $relation = $model->{$name}();
            $model = $relation instanceof MorphTo ? null : $relation->getRelated();
        }

        return $model;
    }

    // a link is not display text, shortening it would break it
    protected function toPlainText(array $values): array
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = $this->toPlainText($value);

                continue;
            }

            if (is_string($value) && ! Str::isUrl($value)) {
                $values[$key] = Str::limit(trim(html_entity_decode(strip_tags($value))));
            }
        }

        return $values;
    }
}
