<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class VacationHeatmapWidget extends Component
{
    use Widgetable;

    #[Locked]
    public array $weeks = [];

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
        return 3;
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
        return view('flux::livewire.widgets.human-resources.vacation-heatmap');
    }

    public function loadData(): void
    {
        $today = today();
        $endDate = $today->copy()->addWeeks(6)->endOfWeek();

        $totalActive = resolve_static(Employee::class, 'query')
            ->employed($today)
            ->count();

        $vacationTypeIds = resolve_static(AbsenceType::class, 'query')
            ->where('affects_vacation', true)
            ->where('is_active', true)
            ->pluck('id');

        $absences = resolve_static(AbsenceRequest::class, 'query')
            ->whereIntegerInRaw('absence_type_id', $vacationTypeIds)
            ->where('state', AbsenceRequestStateEnum::Approved)
            ->where('start_date', '<=', $endDate->toDateString())
            ->where('end_date', '>=', $today->toDateString())
            ->get(['absence_type_id', 'employee_id', 'start_date', 'end_date']);

        $absentEmployeesByDate = [];
        foreach ($absences as $absence) {
            $current = $absence->start_date->copy()->max($today);
            $end = $absence->end_date->copy()->min($endDate);

            while ($current->lte($end)) {
                $dateKey = $current->toDateString();
                $absentEmployeesByDate[$dateKey] ??= [];
                $absentEmployeesByDate[$dateKey][$absence->employee_id] = true;
                $current->addDay();
            }
        }

        $absentCountByDate = array_map('count', $absentEmployeesByDate);

        $startOfWeek = $today->copy()->startOfWeek();
        $this->weeks = [];

        while ($startOfWeek->lte($endDate)) {
            $week = [];
            for ($dayOfWeek = 0; $dayOfWeek < 7; $dayOfWeek++) {
                $date = $startOfWeek->copy()->addDays($dayOfWeek);

                if ($date->lt($today) && ! $date->isSameDay($today)) {
                    $week[] = null;

                    continue;
                }

                if ($date->gt($endDate)) {
                    $week[] = null;

                    continue;
                }

                $dateKey = $date->toDateString();
                $absentCount = $absentCountByDate[$dateKey] ?? 0;
                $percentage = $totalActive > 0
                    ? (int) bcmul(bcdiv($absentCount, $totalActive, 4), '100', 0)
                    : 0;

                $week[] = [
                    'date_formatted' => $date->locale(app()->getLocale())->isoFormat('L'),
                    'day_number' => $date->day,
                    'is_weekend' => $date->isWeekend(),
                    'is_today' => $date->isSameDay($today),
                    'absent_count' => $absentCount,
                    'percentage' => $percentage,
                ];
            }

            $this->weeks[] = $week;
            $startOfWeek->addWeek();
        }
    }
}
