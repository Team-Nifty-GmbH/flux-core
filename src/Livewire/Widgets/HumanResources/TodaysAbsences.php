<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Livewire\HumanResources\HrDashboard;
use FluxErp\Livewire\Support\Widgets\ValueList;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Traits\Widgetable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class TodaysAbsences extends ValueList
{
    use Widgetable;

    public static function dashboardComponent(): array|string
    {
        return HrDashboard::class;
    }

    public function calculateList(): void
    {
        $absences = resolve_static(AbsenceRequest::class, 'query')
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->where('status', 'approved')
            ->with(['user', 'absenceType'])
            ->limit($this->limit)
            ->get();
        
        $this->items = $absences->map(fn (AbsenceRequest $absence) => [
            'id' => $absence->id,
            'label' => $absence->user->name,
            'subLabel' => $absence->absenceType?->name ?? __('Vacation'),
            'growthRate' => $absence->days_requested . ' ' . __('days'),
        ])->toArray();
    }
    
    protected function title(): ?string
    {
        return __('Today\'s Absences');
    }
}