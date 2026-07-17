<?php

namespace FluxErp\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CalendarSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $onlyGroupCalendars = $request->input('onlyGroups', false);

        $query = auth()->user()->calendars();
        if (! blank($request->input('selected')) && blank($request->input('search'))) {
            $selected = $request->input('selected');

            $query->whereKey(Arr::wrap($selected));
        } elseif ($request->has('search')) {
            $query->where(function (Builder $query) use ($request): void {
                foreach (Arr::wrap($request->input('searchFields')) as $field) {
                    $query->orWhere($field, 'like', '%' . $request->input('search') . '%');
                }
            });
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
            $query->orderBy($request->input('orderBy'));
        }

        if ($request->has('orderDirection')) {
            $query->orderBy($request->input('orderDirection'));
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
            $query->whereNull($request->input('whereNull'));
        }

        if ($request->has('whereNotNull')) {
            $query->whereNotNull($request->input('whereNotNull'));
        }

        if ($request->has('select')) {
            $query->select($request->input('select'));
        }

        if ($request->has('whereDoesntHave')) {
            $query->whereDoesntHave($request->input('whereDoesntHave'));
        }

        if ($request->has('whereHas')) {
            $query->whereHas($request->input('whereHas'));
        }

        if ($onlyGroupCalendars) {
            $result = $query
                ->wherePivot('permission', 'owner')
                ->where('is_group', true)
                ->get()
                ->toCalendarObjects()
                ->prepend([
                    'id' => 'my-calendars',
                    'name' => __('My calendars'),
                    'label' => __('My calendars'),
                    'hasNoEvents' => true,
                    'is_group' => true,
                ]);
        } else {
            $result = $query
                ->wherePivot('permission', 'owner')
                ->get()
                ->toCalendarObjects();
        }

        return $result;
    }
}
