<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Models\WorkTime;
use FluxErp\Support\Widgets\ValueList;

class ActiveTaskTimes extends ValueList
{
    public function calculateList(): void
    {
        $query = resolve_static(WorkTime::class, 'query')
            ->where('is_daily_work_time', false)
            ->where('is_locked', false)
            ->with(['user:id,name', 'contact:id,invoice_address_id', 'contact.invoiceAddress'])
            ->get();

        $this->items = $query->map(fn (WorkTime $item) => [
            'id' => $item->id,
            'label' => $item->name
                . ' (' . $item->contact?->invoiceAddress?->getLabel() ?? __('No customer') . ')',
            'subLabel' => $item->user?->name,
            'value' => $item->started_at
                ->locale(app()->getLocale())
                ->timezone(auth()->user()?->timezone ?? config('app.timezone'))
                ->isoFormat('L LT'),
        ])
            ->toArray();
    }
}
