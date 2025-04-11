<?php

namespace FluxErp\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CalendarSearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $onlyGroupCalendars = $request->get('onlyGroups', false);

        $query = auth()->user()->calendars();
        if (! blank($request->get('selected')) && blank($request->get('search'))) {
            $selected = $request->get('selected');

            $query->whereKey(Arr::wrap($selected));
        } elseif ($request->has('search')) {
            $query->where(function (Builder $query) use ($request): void {
                foreach (Arr::wrap($request->get('searchFields')) as $field) {
                    $query->orWhere($field, 'like', '%' . $request->get('search') . '%');
                }
            });
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

        if ($request->has('select')) {
            $query->select($request->get('select'));
        }

        if ($request->has('whereDoesntHave')) {
            $query->whereDoesntHave($request->get('whereDoesntHave'));
        }

        if ($request->has('whereHas')) {
            $query->whereHas($request->get('whereHas'));
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
