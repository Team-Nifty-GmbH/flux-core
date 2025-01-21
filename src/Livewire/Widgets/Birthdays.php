<?php

namespace FluxErp\Livewire\Widgets;

use Carbon\Carbon;
use FluxErp\Models\Address;
use FluxErp\Support\Widgets\ValueList;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Birthdays extends ValueList
{
    public function calculateList(): void
    {
        $query = resolve_static(Address::class, 'query')
            ->where('is_active', true)
            ->whereNotNull('date_of_birth')
            ->whereMonth('date_of_birth', now()->month)
            ->whereDay('date_of_birth', now()->day)
            ->get(['id', 'name', 'date_of_birth']);

        $this->items = $query->map(fn (Address $item) => [
            'id' => $item->id,
            'label' => $item->name,
            'subLabel' => $item->date_of_birth
                ->locale(app()->getLocale())
                ->timezone(auth()->user()?->timezone ?? config('app.timezone'))
                ->isoFormat('L') . ' (' . $item->date_of_birth->diffInYears(now()->startOfDay()) . ')',
            'growthRate' => DataTableButton::make()
                ->icon('eye')
                ->href($item->detailRoute())
                ->attributes(['wire:navigate' => true])
                ->toHtml(),
        ])
            ->toArray();
    }
}
