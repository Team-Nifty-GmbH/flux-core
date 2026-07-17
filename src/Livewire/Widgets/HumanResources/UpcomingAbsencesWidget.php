<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Enums\ChartColorEnum;
use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Lazy]
class UpcomingAbsencesWidget extends Component
{
    use Widgetable;

    #[Locked]
    public array $absences = [];

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
        return 0;
    }

    public static function getDefaultOrderRow(): int
    {
        return 3;
    }

    public static function getDefaultWidth(): int
    {
        return 3;
    }

    public function mount(): void
    {
        $this->loadData();
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.human-resources.upcoming-absences');
    }

    public function loadData(): void
    {
        $today = today();
        $twoWeeksFromNow = $today->copy()->addDays(14);

        $this->absences = resolve_static(AbsenceRequest::class, 'query')
            ->where('state', AbsenceRequestStateEnum::Approved)
            ->where('start_date', '<=', $twoWeeksFromNow)
            ->where('end_date', '>=', $today)
            ->with(['employee:id,name', 'absenceType:id,name,color'])
            ->orderBy('start_date')
            ->get()
            ->map(fn (AbsenceRequest $absence) => [
                'employee_name' => $absence->employee?->name,
                'absence_type' => $absence->absenceType?->name,
                'color' => $absence->absenceType?->color ?? ChartColorEnum::Slate,
                'start_date' => $absence->start_date
                    ->locale(app()->getLocale())
                    ->isoFormat('L'),
                'end_date' => $absence->end_date
                    ->locale(app()->getLocale())
                    ->isoFormat('L'),
                'days' => $absence->days_requested,
            ])
            ->toArray();
    }
}
