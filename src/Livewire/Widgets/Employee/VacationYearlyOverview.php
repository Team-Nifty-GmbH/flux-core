<?php

namespace FluxErp\Livewire\Widgets\Employee;

use Carbon\Carbon;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Enums\EmployeeBalanceAdjustmentTypeEnum;
use FluxErp\Livewire\Employee\Dashboard;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\Employee;
use FluxErp\Traits\Widgetable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Locked;
use Livewire\Component;

class VacationYearlyOverview extends Component
{
    use Widgetable;

    #[Locked]
    public ?int $employeeId = null;

    public array $yearlyData = [];

    public static function dashboardComponent(): array|string
    {
        return Dashboard::class;
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
        return 2;
    }

    public static function getDefaultWidth(): int
    {
        return 6;
    }

    public function mount(): void
    {
        $this->calculateYearlyData();
    }

    public function render(): View
    {
        return view('flux::livewire.widgets.employee.vacation-yearly-overview');
    }

    public function calculateYearlyData(): void
    {
        /** @var Employee $employee */
        $employee = resolve_static(Employee::class, 'query')
            ->whereKey($this->employeeId)
            ->first();

        if (! $employee->employment_date) {
            return;
        }

        $currentYear = now()->year;
        if ($employee->termination_date) {
            $currentYear = min($currentYear, $employee->termination_date->year);
        }

        $startYear = $employee->employment_date->year;
        $endYear = $employee->termination_date ? $employee->termination_date->year : null;

        $this->yearlyData = [];

        $data = [];
        $remainingDays = 0;

        for ($year = $startYear; $year <= $currentYear; $year++) {
            $yearStart = Carbon::create($year);
            $yearEnd = Carbon::create($year, 12, 31);

            // Store the previous year balance before calculating current year
            $carryoverDays = $remainingDays;

            // Use days methods directly
            $earnedDays = $employee->getTotalVacationDays($yearStart, $yearEnd, false);

            // Use the new getUsedVacationDays method (what was actually deducted)
            $usedDays = $employee->getUsedVacationDays($yearStart, $yearEnd);

            // Get requested vacation days (approved absence requests)
            $requestedDays = resolve_static(AbsenceRequest::class, 'query')
                ->where('employee_id', $employee->getKey())
                ->where('state_enum', AbsenceRequestStateEnum::Approved)
                ->where('start_date', '<=', $yearEnd)
                ->where('end_date', '>=', $yearStart)
                ->whereHas('absenceType', function ($query): void {
                    $query->where('affects_vacation', true);
                })
                ->get()
                ->sum(function ($absenceRequest) use ($yearStart, $yearEnd, $employee) {
                    $start = max($absenceRequest->start_date, $yearStart);
                    $end = min($absenceRequest->end_date, $yearEnd);

                    $days = 0;
                    $current = $start->copy();
                    while ($current->lte($end)) {
                        if ($employee->isWorkDay($current)) {
                            $days++;
                        }

                        $current->addDay();
                    }

                    return $days;
                });

            $adjustmentsDays = $employee->employeeBalanceAdjustments()
                ->where('type', EmployeeBalanceAdjustmentTypeEnum::Vacation)
                ->whereYear('effective_date', $year)
                ->sum('amount');

            $availableDays = bcadd(bcadd($carryoverDays, $earnedDays), $adjustmentsDays);
            $remainingDays = bcadd($availableDays, $usedDays);

            $data[] = [
                'year' => $year,
                'carryover_days' => Number::format($carryoverDays, 1),
                'earned_days' => Number::format($earnedDays, 1),
                'adjustments_days' => Number::format($adjustmentsDays, 1),
                'available_days' => Number::format($availableDays, 1),
                'requested_days' => Number::format($requestedDays, 1),
                'used_days' => Number::format($usedDays, 1),
                'remaining_days' => Number::format($remainingDays, 2),
                'is_current' => $year === $currentYear,
                'is_first_year' => $employmentYear = $year === $startYear,
                'is_last_year' => $terminationYear = $endYear && $year === $endYear,
                'employment_date' => $employmentYear
                    ? $employee->employment_date
                        ->locale(app()->getLocale())
                        ->isoFormat('L')
                    : null,
                'termination_date' => $terminationYear
                    ? $employee->termination_date
                        ->locale(app()->getLocale())
                        ->isoFormat('L')
                    : null,
            ];
        }

        $this->yearlyData = array_reverse($data);
    }
}
