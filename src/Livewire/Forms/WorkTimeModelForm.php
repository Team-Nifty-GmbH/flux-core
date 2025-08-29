<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;
use FluxErp\Actions\WorkTimeModel\DeleteWorkTimeModel;
use FluxErp\Actions\WorkTimeModel\UpdateWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class WorkTimeModelForm extends FluxForm
{
    use SupportsAutoRender;

    public ?float $annual_vacation_days = null;

    public ?int $client_id = null;

    public ?int $cycle_weeks = 1;

    #[Locked]
    public ?int $id = null;

    public bool $is_active = true;

    public ?float $max_overtime_hours = null;

    public ?string $name = null;

    public string $overtime_compensation = 'time_off';

    public array $schedules = [];

    public ?float $weekly_break_minutes = null;

    public ?float $weekly_hours = null;

    public ?int $work_days_per_week = null;

    protected static function getModel(): string
    {
        return WorkTimeModel::class;
    }

    public function fill($values): void
    {
        parent::fill($values);

        if ($values && $values->schedules) {
            $this->schedules = [];

            // Group schedules by week_number
            $schedulesByWeek = $values->schedules->groupBy('week_number');

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
            $this->initializeDefaultSchedules();
        }
    }

    public function initializeDefaultSchedules(): void
    {
        $this->schedules = [];

        $numWeeks = $this->cycle_weeks ?: 1;

        for ($weekNum = 1; $weekNum <= $numWeeks; $weekNum++) {
            $weekData = [
                'week_number' => $weekNum,
                'days' => [],
            ];

            // Initialize Monday-Friday with 8 hours, weekend with 0
            for ($day = 1; $day <= 7; $day++) {
                $isWorkDay = $day >= 1 && $day <= 5; // Mon-Fri
                $weekData['days'][$day] = [
                    'weekday' => $day,
                    'start_time' => $isWorkDay ? '08:00' : null,
                    'end_time' => $isWorkDay ? '17:00' : null,
                    'work_hours' => $isWorkDay ? 8.0 : 0.0,
                    'break_minutes' => $isWorkDay ? 60 : 0,
                ];
            }

            $this->schedules[] = $weekData;
        }
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
            $this->initializeDefaultSchedules();
        }
    }

    public function updatedCycleWeeks($value): void
    {
        $currentWeeks = count($this->schedules);
        $newWeeks = (int) $value ?: 1;

        if ($newWeeks > $currentWeeks) {
            // Add new weeks
            for ($weekNum = $currentWeeks + 1; $weekNum <= $newWeeks; $weekNum++) {
                // Copy first week's schedule as template
                $template = $this->schedules[0] ?? null;
                if ($template) {
                    $newWeek = $template;
                    $newWeek['week_number'] = $weekNum;
                    $this->schedules[] = $newWeek;
                } else {
                    // Create default week
                    $weekData = [
                        'week_number' => $weekNum,
                        'days' => [],
                    ];
                    for ($day = 1; $day <= 7; $day++) {
                        $isWorkDay = $day >= 1 && $day <= 5;
                        $weekData['days'][$day] = [
                            'weekday' => $day,
                            'start_time' => $isWorkDay ? '08:00' : null,
                            'end_time' => $isWorkDay ? '17:00' : null,
                            'work_hours' => $isWorkDay ? 8.0 : 0.0,
                            'break_minutes' => $isWorkDay ? 60 : 0,
                        ];
                    }
                    $this->schedules[] = $weekData;
                }
            }
        } elseif ($newWeeks < $currentWeeks) {
            // Remove extra weeks
            $this->schedules = array_slice($this->schedules, 0, $newWeeks);
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
