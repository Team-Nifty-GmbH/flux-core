<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Livewire\HumanResources\HrDashboard;
use FluxErp\Livewire\Support\Widgets\ValueList;
use FluxErp\Models\Holiday;
use FluxErp\Traits\Widgetable;

class UpcomingHolidays extends ValueList
{
    use Widgetable;

    public static function dashboardComponent(): array|string
    {
        return HrDashboard::class;
    }

    public function calculateList(): void
    {
        $holidays = resolve_static(Holiday::class, 'query')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereDate('date', '>=', today())
                    ->orWhere(function ($q) {
                        $q->whereNull('date')
                            ->where('month', '>=', now()->month)
                            ->where('day', '>=', now()->day);
                    });
            })
            ->orderBy('date')
            ->orderBy('month')
            ->orderBy('day')
            ->limit($this->limit)
            ->get();
        
        $this->items = $holidays->map(fn (Holiday $holiday) => [
            'id' => $holiday->id,
            'label' => $holiday->name,
            'subLabel' => $holiday->location?->name ?? __('All Locations'),
            'growthRate' => $holiday->date 
                ? $holiday->date->format('d.m.Y')
                : sprintf('%02d.%02d', $holiday->day, $holiday->month) . ($holiday->is_recurring ? ' (' . __('yearly') . ')' : ''),
        ])->toArray();
    }
    
    protected function title(): ?string
    {
        return __('Upcoming Holidays');
    }
}