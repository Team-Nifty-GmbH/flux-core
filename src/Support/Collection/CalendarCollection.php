<?php

namespace FluxErp\Support\Collection;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class CalendarCollection extends Collection
{
    public function toFlatTree(): SupportCollection
    {
        $tree = [];

        foreach (collect($this->items)->sortBy('parent_id') as $item) {
            $tree[$item->parent_id ?? $item->id][] = $item;
        }

        return collect($tree)->flatten(1);
    }

    public function toCalendarObjects(): SupportCollection
    {
        return $this->map(function ($calendar) {
            $transformed = $calendar->toCalendarObject([
                'permission' => $calendar->pivot->permission ?? null,
                'group' => 'my',
            ]);

            if ($calendar->children instanceof static && $calendar->children->isNotEmpty()) {
                $transformed['children'] = $calendar->children->toCalendarObjects();
            } else {
                data_forget($transformed, 'children');
            }

            return $transformed;
        });
    }
}
