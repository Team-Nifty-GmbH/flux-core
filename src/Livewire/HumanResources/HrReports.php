<?php

namespace FluxErp\Livewire\HumanResources;

use FluxErp\Models\Location;
use FluxErp\Models\User;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class HrReports extends Component
{
    use Actions;

    public ?string $reportType = 'vacation_overview';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public ?int $locationId = null;

    public ?int $workTimeModelId = null;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    #[Computed]
    public function locations(): array
    {
        return resolve_static(Location::class, 'query')
            ->where('is_active', true)
            ->get(['id', 'name'])
            ->toArray();
    }

    #[Computed]
    public function workTimeModels(): array
    {
        return resolve_static(WorkTimeModel::class, 'query')
            ->where('is_active', true)
            ->get(['id', 'name'])
            ->toArray();
    }

    #[Computed]
    public function vacationOverview(): array
    {
        $query = resolve_static(User::class, 'query')
            ->where(function (Builder $query) {
                $query->whereNull('termination_date')
                    ->orWhere('termination_date', '>', $this->dateTo);
            })
            ->whereHas('workTimeModel')
            ->with(['workTimeModel', 'location']);

        if ($this->locationId) {
            $query->where('location_id', $this->locationId);
        }

        if ($this->workTimeModelId) {
            $query->where('work_time_model_id', $this->workTimeModelId);
        }

        $users = $query->get();

        $data = [];

        foreach ($users as $user) {
            $totalVacationDays = $user->workTimeModel?->vacation_days ?? 0;

            $usedDays = resolve_static(AbsenceRequest::class, 'query')
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereRelation('absenceType', 'is_vacation', true)
                ->whereBetween('start_date', [$this->dateFrom, $this->dateTo])
                ->sum('days_requested');

            $pendingDays = resolve_static(AbsenceRequest::class, 'query')
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->whereRelation('absenceType', 'is_vacation', true)
                ->whereBetween('start_date', [$this->dateFrom, $this->dateTo])
                ->sum('days_requested');

            $data[] = [
                'employee' => $user->name,
                'employee_number' => $user->employee_number,
                'location' => $user->location?->name,
                'work_model' => $user->workTimeModel?->name,
                'total_days' => $totalVacationDays,
                'used_days' => $usedDays,
                'pending_days' => $pendingDays,
                'remaining_days' => $totalVacationDays - $usedDays,
            ];
        }

        return $data;
    }

    #[Computed]
    public function absenceReport(): array
    {
        $query = resolve_static(AbsenceRequest::class, 'query')
            ->whereIn('status', ['approved', 'pending'])
            ->whereBetween('start_date', [$this->dateFrom, $this->dateTo])
            ->with(['user', 'absenceType']);

        if ($this->locationId) {
            $query->whereHas('user', function ($q) {
                $q->where('location_id', $this->locationId);
            });
        }

        if ($this->workTimeModelId) {
            $query->whereHas('user', function ($q) {
                $q->where('work_time_model_id', $this->workTimeModelId);
            });
        }

        return $query->get()->map(function ($request) {
            return [
                'employee' => $request->user->name,
                'type' => $request->absenceType->name,
                'start_date' => $request->start_date->format('Y-m-d'),
                'end_date' => $request->end_date->format('Y-m-d'),
                'days' => $request->days_requested,
                'status' => $request->status,
                'substitute' => $request->substituteUser?->name,
            ];
        })->toArray();
    }

    #[Computed]
    public function workTimeReport(): array
    {
        $query = resolve_static(WorkTime::class, 'query')
            ->where('is_locked', true)
            ->whereBetween('started_at', [$this->dateFrom, $this->dateTo])
            ->with(['user']);

        if ($this->locationId) {
            $query->whereHas('user', function ($q) {
                $q->where('location_id', $this->locationId);
            });
        }

        $workTimes = $query->get();

        $grouped = $workTimes->groupBy('user_id');

        $data = [];

        foreach ($grouped as $userId => $times) {
            $user = $times->first()->user;
            $totalHours = $times->sum('total_time_ms') / 3600000;
            $billableHours = $times->where('is_billable', true)->sum('total_time_ms') / 3600000;

            $data[] = [
                'employee' => $user->name,
                'employee_number' => $user->employee_number,
                'total_hours' => round($totalHours, 2),
                'billable_hours' => round($billableHours, 2),
                'non_billable_hours' => round($totalHours - $billableHours, 2),
                'days_worked' => $times->pluck('started_at')->map->format('Y-m-d')->unique()->count(),
            ];
        }

        return $data;
    }

    #[Computed]
    public function overtimeReport(): array
    {
        $query = resolve_static(User::class, 'query')
            ->whereNotNull('employee_number')
            ->where('is_active', true)
            ->whereNotNull('work_time_model_id')
            ->with(['workTimeModel', 'workTimes' => function ($q) {
                $q->where('is_locked', true)
                    ->whereBetween('started_at', [$this->dateFrom, $this->dateTo]);
            }]);

        if ($this->locationId) {
            $query->where('location_id', $this->locationId);
        }

        if ($this->workTimeModelId) {
            $query->where('work_time_model_id', $this->workTimeModelId);
        }

        $users = $query->get();

        $data = [];

        foreach ($users as $user) {
            $targetHours = $user->workTimeModel->weekly_hours *
                Carbon::parse($this->dateFrom)->diffInWeeks(Carbon::parse($this->dateTo));

            $actualHours = $user->workTimes->sum('total_time_ms') / 3600000;
            $overtimeHours = max(0, $actualHours - $targetHours);

            if ($overtimeHours > 0) {
                $data[] = [
                    'employee' => $user->name,
                    'employee_number' => $user->employee_number,
                    'target_hours' => round($targetHours, 2),
                    'actual_hours' => round($actualHours, 2),
                    'overtime_hours' => round($overtimeHours, 2),
                    'overtime_percentage' => round(($overtimeHours / $targetHours) * 100, 1),
                ];
            }
        }

        return $data;
    }

    #[Computed]
    public function reportData(): array
    {
        return match ($this->reportType) {
            'vacation_overview' => $this->vacationOverview,
            'absence_report' => $this->absenceReport,
            'worktime_report' => $this->workTimeReport,
            'overtime_report' => $this->overtimeReport,
            default => [],
        };
    }

    public function exportCsv(): void
    {
        $data = $this->reportData;

        if (empty($data)) {
            $this->toast()->error(__('No data to export'))->send();

            return;
        }

        $headers = array_keys($data[0]);
        $filename = $this->reportType . '_' . now()->format('Y-m-d_His') . '.csv';

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);

        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        $this->js(<<<JS
            const blob = new Blob([`$content`], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = '$filename';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        JS);
    }

    public function render()
    {
        return view('flux::livewire.hr-reports');
    }
}
