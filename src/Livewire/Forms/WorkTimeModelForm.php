<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;
use FluxErp\Actions\WorkTimeModel\DeleteWorkTimeModel;
use FluxErp\Actions\WorkTimeModel\UpdateWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Models\WorkTimeModelSchedule;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class WorkTimeModelForm extends FluxForm
{
    use SupportsAutoRender;
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public ?int $cycle_weeks = 1;

    public ?float $weekly_hours = null;

    public ?int $annual_vacation_days = null;

    public ?float $max_overtime_hours = null;

    public string $overtime_compensation = 'time_off';

    public bool $has_core_hours = false;

    public ?string $core_hours_start = null;

    public ?string $core_hours_end = null;

    public bool $is_active = true;

    public ?int $client_id = null;

    public array $schedules = [];

    public function fill($model): void
    {
        parent::fill($model);
        
        if ($model && $model->schedules) {
            $this->schedules = [];
            
            // Group schedules by week_number
            $schedulesByWeek = $model->schedules->groupBy('week_number');
            
            foreach ($schedulesByWeek as $weekNumber => $weekSchedules) {
                $weekData = [
                    'week_number' => $weekNumber,
                    'days' => []
                ];
                
                // Initialize all 7 days
                for ($day = 1; $day <= 7; $day++) {
                    $schedule = $weekSchedules->firstWhere('weekday', $day);
                    $weekData['days'][$day] = [
                        'weekday' => $day,
                        'start_time' => $schedule?->start_time ?? null,
                        'end_time' => $schedule?->end_time ?? null,
                        'work_hours' => $schedule?->work_hours ?? 0,
                        'break_minutes' => $schedule?->break_minutes ?? 0,
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
                'days' => []
            ];
            
            // Initialize Monday-Friday with 8 hours, weekend with 0
            for ($day = 1; $day <= 7; $day++) {
                $isWorkDay = $day >= 1 && $day <= 5; // Mon-Fri
                $weekData['days'][$day] = [
                    'weekday' => $day,
                    'start_time' => $isWorkDay ? '08:00' : null,
                    'end_time' => $isWorkDay ? '17:00' : null,
                    'work_hours' => $isWorkDay ? 8 : 0,
                    'break_minutes' => $isWorkDay ? 60 : 0,
                ];
            }
            
            $this->schedules[] = $weekData;
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
                        'days' => []
                    ];
                    for ($day = 1; $day <= 7; $day++) {
                        $isWorkDay = $day >= 1 && $day <= 5;
                        $weekData['days'][$day] = [
                            'weekday' => $day,
                            'start_time' => $isWorkDay ? '08:00' : null,
                            'end_time' => $isWorkDay ? '17:00' : null,
                            'work_hours' => $isWorkDay ? 8 : 0,
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

    protected static function getModel(): string
    {
        return WorkTimeModel::class;
    }
    
    public function save(): void
    {
        parent::save();
        
        if ($this->id) {
            $model = WorkTimeModel::find($this->id);
            
            if ($model && !empty($this->schedules)) {
                // Delete existing schedules
                $model->schedules()->delete();
                
                // Create new schedules
                foreach ($this->schedules as $week) {
                    foreach ($week['days'] as $day => $dayData) {
                        WorkTimeModelSchedule::create([
                            'work_time_model_id' => $model->id,
                            'week_number' => $week['week_number'],
                            'weekday' => $dayData['weekday'],
                            'start_time' => $dayData['start_time'],
                            'end_time' => $dayData['end_time'],
                            'work_hours' => $dayData['work_hours'] ?? 0,
                            'break_minutes' => $dayData['break_minutes'] ?? 0,
                        ]);
                    }
                }
            }
        }
    }
}