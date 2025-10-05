<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;
use FluxErp\Actions\WorkTimeModel\DeleteWorkTimeModel;
use FluxErp\Actions\WorkTimeModel\UpdateWorkTimeModel;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

class WorkTimeModelForm extends FluxForm
{
    use SupportsAutoRender;

    public ?float $annual_vacation_days = null;

    public ?int $cycle_weeks = 1;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?float $max_overtime_hours = null;

    public ?string $name = null;

    public string $overtime_compensation_enum = 'time_off';

    public array $schedules = [];

    public ?float $weekly_hours = null;

    public ?int $work_days_per_week = null;

    public function fill($values): void
    {
        parent::fill($values);

        if ($values instanceof Model) {
            $this->loadSchedules($values);
        }
    }

    public function getDefaultWeekSchedule(?int $weekNumber = null, ?int $days = null): array
    {
        $defaultWeek = [
            'week_number' => $weekNumber,
            'days' => [],
        ];
        for ($day = 1; $day <= 7; $day++) {
            $isWorkDay = $day >= 1 && $day <= ($days ?? $this->work_days_per_week ?? 5);
            $defaultWeek['days'][$day] = [
                'weekday' => $day,
                'start_time' => $isWorkDay ? '08:00' : null,
                'end_time' => $isWorkDay ? '17:00' : null,
                'work_hours' => $isWorkDay ? 8.0 : 0.0,
                'break_minutes' => $isWorkDay ? 60 : 0,
            ];
        }

        return $defaultWeek;
    }

    public function loadSchedules($model): void
    {
        if ($model && $model->schedules) {
            $this->schedules = [];

            // Group schedules by week_number
            $schedulesByWeek = $model->schedules->groupBy('week_number');

            foreach ($schedulesByWeek as $weekNumber => $weekSchedules) {
                $weekData = [
                    'week_number' => $weekNumber,
                    'days' => [],
                ];

                // Initialize all 7 days
                for ($day = 1; $day <= 7; $day++) {
                    $schedule = $weekSchedules->firstWhere('weekday', $day);
                    $weekData['days'][$day] = [
                        'weekday' => $day,
                        'start_time' => $schedule?->start_time ?? null,
                        'end_time' => $schedule?->end_time ?? null,
                        'work_hours' => (float) ($schedule?->work_hours ?? 0),
                        'break_minutes' => (int) ($schedule?->break_minutes ?? 0),
                    ];
                }

                $this->schedules[] = $weekData;
            }
        }

        // If no schedules exist, initialize with default week
        if (empty($this->schedules)) {
            $this->schedules = [];

            for ($weekNumber = 1; $weekNumber <= ($this->cycle_weeks ?: 1); $weekNumber++) {
                $this->schedules[] = $this->getDefaultWeekSchedule($weekNumber);
            }
        }
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateWorkTimeModel::class,
            'update' => UpdateWorkTimeModel::class,
            'delete' => DeleteWorkTimeModel::class,
        ];
    }
}
