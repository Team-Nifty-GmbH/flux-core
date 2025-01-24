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

        $this->items = $query->map(fn (WorkTime $item) => [
            'id' => $item->id,
            'label' => '<div class="flex gap-1.5 items-center">' .
                    (
                        $item->user
                            ->workTimes()
                            ->where('is_daily_work_time', true)
                            ->where('is_pause', true)
                            ->where('is_locked', false)
                            ->exists()
                        ? '<span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-warning-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-warning-500"></span>
                           </span>'
                        : '<span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-positive-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-positive-500"></span>
                           </span>'
                    )
                    . '<div>' . $item->user?->name . '</div>
                </div>',
            'value' => $item->started_at
                ->locale(app()->getLocale())
                ->timezone(auth()->user()?->timezone ?? config('app.timezone'))
                ->isoFormat('L LT'),
            'growthRate' => null,
        ])->toArray();
    }
}
