<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Livewire\Support\Widgets\ValueList;
use FluxErp\Models\WorkTime;

class ActiveWorkTimesWidget extends ValueList
{
    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
    }

    public static function getCategory(): ?string
    {
        return 'Human Resources';
    }

    public static function getDefaultHeight(): int
    {
        return 2;
    }

    public static function getDefaultOrderColumn(): int
    {
        return 4;
    }

    public static function getDefaultOrderRow(): int
    {
        return 4;
    }

    public static function getDefaultWidth(): int
    {
        return 2;
    }

    public function calculateList(): void
    {
        $workTimes = resolve_static(WorkTime::class, 'query')
            ->where('is_daily_work_time', true)
            ->where('is_locked', false)
            ->where('is_pause', false)
            ->with(['user:id,name', 'employee:id,name'])
            ->get();

        $usersWithPause = resolve_static(WorkTime::class, 'query')
            ->whereIntegerInRaw('user_id', $workTimes->pluck('user_id'))
            ->where('is_daily_work_time', true)
            ->where('is_locked', false)
            ->where('is_pause', true)
            ->pluck('user_id')
            ->flip();

        $this->items = $workTimes->map(fn (WorkTime $item) => [
            'id' => $item->getKey(),
            'label' => '<div class="flex gap-1.5 items-center">' .
                    (
                        $usersWithPause->has($item->user_id)
                        ? '<span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                           </span>'
                        : '<span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                           </span>'
                    )
                    . '<div>' . ($item->user?->name ?? $item->employee?->name) . '</div>
                </div>',
            'value' => $item->started_at
                ->locale(app()->getLocale())
                ->timezone(auth()->user()?->timezone ?? config('app.timezone'))
                ->isoFormat('L LT'),
            'growthRate' => null,
        ])
            ->toArray();
    }

    protected function getListeners(): array
    {
        return [
            'echo-private:' . resolve_static(WorkTime::class, 'getBroadcastChannel')
                . ',.WorkTimeDailyUpdated' => 'calculateList',
        ];
    }
}
