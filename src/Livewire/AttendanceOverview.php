<?php

namespace FluxErp\Livewire;

use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Holiday;
use FluxErp\Models\User;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Component;

class AttendanceOverview extends Component
{
    public int $year;
    public int $month;
    public array $absenceTypes = [];
    public array $attendanceData = [];
    public array $holidays = [];
    public array $dailySummary = [];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->month = now()->month;
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->loadAbsenceTypes();
        $this->loadHolidays();
        $this->loadAttendanceData();
        $this->calculateDailySummary();
    }

    protected function loadAbsenceTypes(): void
    {
        $this->absenceTypes = resolve_static(AbsenceType::class, 'query')
            ->where('is_active', true)
            ->get()
            ->map(fn($type) => [
                'id' => $type->id,
                'name' => $type->name,
                'color' => $type->color,
            ])
            ->toArray();
    }

    protected function loadHolidays(): void
    {
        $startDate = Carbon::create($this->year, $this->month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $this->holidays = resolve_static(Holiday::class, 'query')
            ->whereBetween('date', [$startDate, $endDate])
            ->where(function ($query) {
                $query->whereNull('location_id')
                    ->orWhereHas('location.users', function ($q) {
                        $q->where('users.id', auth()->id());
                    });
            })
            ->pluck('date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->toArray();
    }

    protected function loadAttendanceData(): void
    {
        $startDate = Carbon::create($this->year, $this->month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $users = resolve_static(User::class, 'query')
            ->where(function (Builder $query) use ($endDate) {
                $query->whereNull('termination_date')
                    ->orWhere('termination_date', '>', $endDate);
            })
            ->whereHas('workTimeModel')
            ->with(['workTimeModel' => function($query) {
                $query->with('schedules');
            }])
            ->orderBy('name')
            ->get();

        $absenceRequests = resolve_static(AbsenceRequest::class, 'query')
            ->whereIn('user_id', $users->pluck('id'))
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->with('absenceType')
            ->get();

        // Load WorkTime entries for the month
        $workTimes = resolve_static(WorkTime::class, 'query')
            ->whereIn('user_id', $users->pluck('id'))
            ->where('is_daily_work_time', true)
            ->whereBetween('started_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get()
            ->groupBy(function($item) {
                return $item->user_id . '-' . $item->started_at->format('Y-m-d');
            });

        $this->attendanceData = [];

        foreach ($users as $user) {
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'days' => [],
            ];

            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $dateString = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeek; // 0 = Sunday, 1 = Monday, ..., 6 = Saturday

                // Check if user should work on this day according to their work time model
                $shouldWork = false;
                if ($user->workTimeModel && $user->workTimeModel->schedules) {
                    // weekday in database: 1 = Monday, 2 = Tuesday, ..., 7 = Sunday
                    // Carbon dayOfWeek: 0 = Sunday, 1 = Monday, ..., 6 = Saturday
                    // Convert Carbon dayOfWeek to database weekday format
                    $dbWeekday = $dayOfWeek === 0 ? 7 : $dayOfWeek;

                    // Check all schedules for this weekday (could be multiple weeks)
                    $daySchedules = $user->workTimeModel->schedules->filter(function($schedule) use ($dbWeekday) {
                        return $schedule->weekday == $dbWeekday;
                    });

                    // If any schedule for this day has work_hours > 0, it's a working day
                    $shouldWork = $daySchedules->contains(function($schedule) {
                        return $schedule->work_hours > 0;
                    });
                } else {
                    // If no work time model, assume Mon-Fri are working days
                    $shouldWork = !$currentDate->isWeekend();
                }

                $dayData = [
                    'date' => $dateString,
                    'day' => $currentDate->day,
                    'isWeekend' => $currentDate->isWeekend(),
                    'isHoliday' => in_array($dateString, $this->holidays),
                    'shouldWork' => $shouldWork,
                    'hasWorkTime' => false,
                    'workTime' => null,
                    'absence' => null,
                    'status' => 'none', // none, present, absent, absence_approved
                ];

                // Check for work time entry
                $workTimeKey = $user->id . '-' . $dateString;
                if ($workTimes->has($workTimeKey)) {
                    $workTime = $workTimes->get($workTimeKey)->first();
                    $dayData['hasWorkTime'] = true;
                    $dayData['workTime'] = [
                        'started_at' => $workTime->started_at->format('H:i'),
                        'ended_at' => $workTime->ended_at?->format('H:i'),
                        'total_time_ms' => $workTime->total_time_ms,
                        'is_locked' => $workTime->is_locked,
                    ];
                    $dayData['status'] = 'present';
                }

                // Check for absence
                foreach ($absenceRequests->where('user_id', $user->id) as $absence) {
                    if ($currentDate->between($absence->start_date, $absence->end_date)) {
                        $dayData['absence'] = [
                            'type_id' => $absence->absence_type_id,
                            'color' => $absence->absenceType->color,
                            'name' => $absence->absenceType->name,
                        ];
                        $dayData['status'] = 'absence_approved';
                        break;
                    }
                }

                // If no work time and no absence, but should work -> absent
                // Only mark as absent if it's a working day (not weekend/holiday)
                if (!$dayData['hasWorkTime'] && !$dayData['absence'] && $shouldWork && !$dayData['isWeekend'] && !$dayData['isHoliday']) {
                    $dayData['status'] = 'absent';
                }

                $userData['days'][] = $dayData;
                $currentDate->addDay();
            }

            $this->attendanceData[] = $userData;
        }
    }

    protected function calculateDailySummary(): void
    {
        $startDate = Carbon::create($this->year, $this->month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $this->dailySummary = [];

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dayIndex = $currentDate->day - 1;

            $summary = [
                'total' => 0,
                'present' => 0,
                'absent' => 0,
                'absences' => [],
            ];

            // Initialize all absence types to 0
            foreach ($this->absenceTypes as $type) {
                $summary['absences'][$type['id']] = 0;
            }

            // Count employees for each day
            foreach ($this->attendanceData as $userData) {
                if (!isset($userData['days'][$dayIndex])) {
                    continue;
                }

                $day = $userData['days'][$dayIndex];


                // Don't skip holidays - they should be counted if it's a working day for the employee
                // Only skip if it's not a working day according to the work time model
                if (!$day['shouldWork']) {
                    // But still count absences even on non-working days
                    if ($day['status'] === 'absence_approved' && isset($day['absence']['type_id'])) {
                        $summary['absences'][$day['absence']['type_id']]++;
                    }
                    continue;
                }

                // This is a working day for this employee, count them
                $summary['total']++;

                switch ($day['status']) {
                    case 'present':
                        $summary['present']++;
                        break;
                    case 'absent':
                        $summary['absent']++;
                        break;
                    case 'absence_approved':
                        if (isset($day['absence']['type_id'])) {
                            $summary['absences'][$day['absence']['type_id']]++;
                        }
                        break;
                }
            }

            $this->dailySummary[] = $summary;
            $currentDate->addDay();
        }
    }

    public function previousMonth(): void
    {
        $this->month--;
        if ($this->month < 1) {
            $this->month = 12;
            $this->year--;
        }
        $this->loadData();
    }

    public function nextMonth(): void
    {
        $this->month++;
        if ($this->month > 12) {
            $this->month = 1;
            $this->year++;
        }
        $this->loadData();
    }

    public function render()
    {
        return view('flux::livewire.attendance-overview', [
            'monthName' => Carbon::create($this->year, $this->month)->locale('de')->monthName,
            'daysInMonth' => Carbon::create($this->year, $this->month)->daysInMonth,
        ]);
    }
}
