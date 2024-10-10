<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\WorkTime;
use FluxErp\Support\Widgets\ValueList;

class ActiveTaskTimes extends ValueList
{
    public function calculateList(): void
    {
        $query = resolve_static(WorkTime::class, 'query')
            ->where('is_locked', false)
            ->where('is_daily_work_time', false)
            ->with(['user:id,name', 'contact.invoiceAddress'])
            ->get();

        $this->items = $query->map(fn ($item) => [
            'id' => $item->id,
            'label' => $item->name . ' (' . $item->contact->invoiceAddress->getLabel() . ')',
            'subLabel' => $item->user?->name,
            'value' => $item->started_at
                ->locale(app()->getLocale())
                ->timezone(auth()->user()?->timezone ?? config('app.timezone'))
                ->isoFormat('L LT'),
        ])->toArray();
    }
}
