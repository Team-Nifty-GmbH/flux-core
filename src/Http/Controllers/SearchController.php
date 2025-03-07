<?php

namespace FluxErp\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Laravel\Scout\Searchable;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class SearchController extends Controller
{
    public function __invoke(Request $request, $model)
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
            || (! $isSearchable && ! $request->get('searchFields'))
        ) {
            abort(404);
        }

        Event::dispatch('tall-datatables-searching', $request);

        if ($request->has('selected') && ! $request->has('search')) {
            $selected = $request->get('selected');
            $optionValue = $request->get('option-value') ?: (app($model))->getKeyName();

            $query = resolve_static($model, 'query');
            is_array($selected)
                ? $query->whereIn($optionValue, Arr::wrap($selected))
                : $query->where($optionValue, $selected);
        } elseif ($request->has('search') && $isSearchable) {
            $query = ! is_string($request->get('search'))
                ? resolve_static($model, 'query')->limit(20)
                : resolve_static($model, 'search', ['query' => $request->get('search')])
                    ->toEloquentBuilder();
        } elseif ($request->has('search')) {
            $query = resolve_static($model, 'query');
            $query->where(function (Builder $query) use ($request) {
                foreach (Arr::wrap($request->get('searchFields')) as $field) {
                    $query->orWhere($field, 'like', '%' . $request->get('search') . '%');
                }
            });
        } else {
            /** @var \Illuminate\Database\Eloquent\Builder $query */
            $query = resolve_static($model, 'query');
        }

        if ($request->has('with')) {
            $query->with($request->get('with'));
        }

        if ($request->has('limit')) {
            $query->limit($request->get('limit'));
        } else {
            $query->limit(10);
        }

        if ($request->has('orderBy')) {
            $query->orderBy($request->get('orderBy'));
        }

        if ($request->has('orderDirection')) {
            $query->orderBy($request->get('orderDirection'));
        }

        if ($request->has('where')) {
            $query->where($request->get('where'));
        }

        if ($request->has('whereIn')) {
            foreach ($request->get('whereIn') as $whereIn) {
                $whereIn[1] = Arr::wrap($whereIn[1]);
                $query->whereIn(...$whereIn);
            }
        }

        if ($request->has('whereNotIn')) {
            foreach ($request->get('whereNotIn') as $whereNotIn) {
                $query->whereNotIn(...$whereNotIn);
            }
        }

        if ($request->has('whereNull')) {
            $query->whereNull($request->get('whereNull'));
        }

        if ($request->has('whereNotNull')) {
            $query->whereNotNull($request->get('whereNotNull'));
        }

        if ($request->has('whereBetween')) {
            $query->whereBetween(...$request->get('whereBetween'));
        }

        if ($request->has('whereNotBetween')) {
            $query->whereNotBetween(...$request->get('whereNotBetween'));
        }

        if ($request->has('whereDate')) {
            $query->whereDate(...$request->get('whereDate'));
        }

        if ($request->has('whereMonth')) {
            $query->whereMonth(...$request->get('whereMonth'));
        }

        if ($request->has('whereDay')) {
            $query->whereDay(...$request->get('whereDay'));
        }

        if ($request->has('whereYear')) {
            $query->whereYear(...$request->get('whereYear'));
        }

        if ($request->has('whereTime')) {
            $query->whereTime(...$request->get('whereTime'));
        }

        if ($request->has('select')) {
            $query->select($request->get('select'));
        }

        if ($request->has('whereDoesntHave')) {
            $query->whereDoesntHave($request->get('whereDoesntHave'));
        }

        if ($request->has('whereHas')) {
            $query->whereHas($request->get('whereHas'));
        }

        $result = $query->latest()->get();

        if ($request->has('appends')) {
            $result->each(function ($item) use ($request) {
                $item->append(array_intersect($item->getAppends(), $request->get('appends')));
            });
        }

        if (is_a(app($model), InteractsWithDataTables::class)) {
            $result = $result->map(function ($item) use ($request) {
                return array_merge(
                    [
                        'id' => $item->getKey(),
                        'label' => $item->getLabel(),
                        'description' => $item->getDescription(),
                        'image' => $item->getAvatarUrl(),
                    ],
                    $item->only($request->get('fields', [])),
                    $item->only($request->get('appends', [])),
                );
            });
        }

        Event::dispatch('tall-datatables-searched', [$request, $result]);

        return $result;
    }
}
