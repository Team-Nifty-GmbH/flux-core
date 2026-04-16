<?php

namespace FluxErp\Livewire\Widgets\HumanResources;

use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Livewire\HumanResources\Dashboard;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\Employee;
use FluxErp\Traits\Livewire\Widget\Widgetable;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class VacationHeatmapWidget extends Component
{
    use Widgetable;

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
        $this->loadHeatmap();
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.human-resources.vacation-heatmap');
    }

    public function loadHeatmap(): void
    {
        $today = now()->startOfDay();
        $endDate = $today->copy()->addWeeks(6)->endOfWeek();

        $totalActive = resolve_static(Employee::class, 'query')
            ->employed(now())
            ->count();

        $absences = resolve_static(AbsenceRequest::class, 'query')
            ->where('state', AbsenceRequestStateEnum::Approved)
            ->where('end_date', '>=', $today->toDateString())
            ->where('start_date', '<=', $endDate->toDateString())
            ->get(['id', 'employee_id', 'start_date', 'end_date']);

        $absentCountByDate = [];
        foreach ($absences as $absence) {
            $current = $absence->start_date->copy()->max($today);
            $end = $absence->end_date->copy()->min($endDate);

            while ($current->lte($end)) {
                $dateKey = $current->toDateString();
                $absentCountByDate[$dateKey] = ($absentCountByDate[$dateKey] ?? collect())->push($absence->employee_id);
                $current->addDay();
            }
        }

        foreach ($absentCountByDate as $dateKey => $employeeIds) {
            $absentCountByDate[$dateKey] = $employeeIds->unique()->count();
        }

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
