<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\WorkTime;
use FluxErp\Support\Widgets\ValueList;

class ActiveDailyWorkTimes extends ValueList
{
    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(WorkTime::class, 'getBroadcastChannel')
                . ',.WorkTimeDailyUpdated' => 'calculateList',
        ];
    }

    public function calculateList(): void
    {
        $query = resolve_static(WorkTime::class, 'query')
            ->where('is_daily_work_time', true)
            ->where('is_locked', false)
            ->where('is_pause', false)
            ->with('user:id,name')
            ->get();

        $this->items = $query->map(fn ($item) => [
            'id' => $item->id,
            'label' => $item->user?->name,
            'value' => $item->started_at
                ->locale(app()->getLocale())
                ->timezone(auth()->user()?->timezone ?? config('app.timezone'))
                ->isoFormat('L LT'),
            'growthRate' => null,
        ])->toArray();
    }
}
