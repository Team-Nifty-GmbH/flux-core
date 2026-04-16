<?php

namespace FluxErp\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Laravel\Scout\Searchable;
use Laravel\Scout\SearchableScope;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class SearchController extends Controller
{
    public function __invoke(Request $request, ?string $model = null)
    {
        // check if $model is a morph alias
        $model = morphed_model($model) ?? $model;
        $model = qualify_model(str_replace('/', '\\', $model));
        $isSearchable = in_array(
            Searchable::class,
            class_uses_recursive(resolve_static($model, 'class'))
        );

        if (
            ! class_exists($model)
            || (! $isSearchable && ! $request->input('searchFields'))
        ) {
            abort(404);
        }

        Event::dispatch('tall-datatables-searching', $request);

        if (! blank($request->input('selected')) && blank($request->input('search'))) {
            $selected = $request->input('selected');
            $optionValue = $request->input('option-value') ?: (app($model))->getKeyName();

            $query = resolve_static($model, 'query');
            is_array($selected)
                ? $query->whereIn($optionValue, Arr::wrap($selected))
                : $query->where($optionValue, $selected);
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
                    ->toEloquentBuilder(perPage: $perPageSearch);
        } elseif ($request->has('search')) {
            $query = resolve_static($model, 'query');
            $query->where(function (Builder $query) use ($request): void {
                foreach (Arr::wrap($request->input('searchFields')) as $field) {
                    $query->orWhere($field, 'like', '%' . $request->input('search') . '%');
                }
            });
        } else {
            /** @var Builder $query */
            $query = resolve_static($model, 'query');
        }

        if ($request->has('with')) {
            $query->with($request->input('with'));
        }

        if ($request->has('limit')) {
            $query->limit($request->input('limit'));
        } else {
            $query->limit(10);
        }

        if ($request->has('orderBy')) {
            $query->orderBy($request->input('orderBy'), $request->input('orderDirection', 'asc'));
        }

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
            $query->select($request->input('select'));
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

        $result = $query->latest()->get();

        if ($request->has('appends')) {
            $result->each(function ($item) use ($request): void {
                $item->append(array_intersect($item->getAppends(), $request->input('appends')));
            });
        }

        if (is_a(app($model), InteractsWithDataTables::class)) {
            $result = $result->map(function ($item) use ($request) {
                return array_merge(
                    [
                        'id' => $item->getKey(),
                        'label' => $item->getLabel() ?? '-',
                        'description' => $item->getDescription(),
                        'image' => $item->getAvatarUrl(),
                    ],
                    $item->only($request->input('fields', [])),
                    $item->only($request->input('appends', [])),
                );
            });
        }

        Event::dispatch('tall-datatables-searched', [$request, $result]);

        return $result;
    }
}
