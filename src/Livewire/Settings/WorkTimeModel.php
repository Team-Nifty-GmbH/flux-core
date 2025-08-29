<?php

namespace FluxErp\Livewire\Settings;

use Exception;
use FluxErp\Actions\WorkTimeModel\DeleteWorkTimeModel;
use FluxErp\Actions\WorkTimeModel\UpdateWorkTimeModel;
use FluxErp\Livewire\Forms\WorkTimeModelForm;
use FluxErp\Models\WorkTimeModel as WorkTimeModelModel;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class WorkTimeModel extends Component
{
    use Actions;

    public WorkTimeModelForm $workTimeModelForm;

    public function mount(string $id): void
    {
        $model = resolve_static(WorkTimeModelModel::class, 'query')
            ->with('schedules')
            ->whereKey($id)
            ->firstOrFail();

        $this->workTimeModelForm->fill($model);
        $this->workTimeModelForm->loadSchedules($model);
    }

    public function render(): View
    {
        return view('flux::livewire.settings.work-time-model');
    }

    public function delete(): void
    {
        try {
            DeleteWorkTimeModel::make($this->workTimeModelForm->toArray())
                ->checkPermission()
                ->validate()
                ->execute();

            $this->redirect(route('settings.work-time-models'));
        } catch (Exception $e) {
            exception_to_notifications($e, $this);
        }
    }

    #[Renderless]
    #[On('cycle-weeks-updated')]
    public function handleCycleWeeksUpdate(): void
    {
        $currentWeeks = count($this->workTimeModelForm->schedules);
        $newWeeks = (int) $this->workTimeModelForm->cycle_weeks ?: 1;

        if ($newWeeks > $currentWeeks) {
            // Add new weeks
            for ($weekNum = $currentWeeks + 1; $weekNum <= $newWeeks; $weekNum++) {
                // Copy first week's schedule as template
                $template = $this->workTimeModelForm->schedules[0] ?? null;
                if ($template) {
                    $newWeek = $template;
                    $newWeek['week_number'] = $weekNum;
                    $this->workTimeModelForm->schedules[] = $newWeek;
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
                            'work_hours' => $isWorkDay ? 8 : 0,
                            'break_minutes' => $isWorkDay ? 60 : 0,
                        ];
                    }
                    $this->workTimeModelForm->schedules[] = $weekData;
                }
            }
        } elseif ($newWeeks < $currentWeeks) {
            // Remove extra weeks
            $this->workTimeModelForm->schedules = array_slice($this->workTimeModelForm->schedules, 0, $newWeeks);
        }
    }

    public function save(): void
    {
        try {
            // Get all form data including schedules
            $data = $this->workTimeModelForm->toArray();

            // Update the work time model with schedules
            $model = UpdateWorkTimeModel::make($data)
                ->checkPermission()
                ->validate()
                ->execute();

            $this->toast()->success(__('Work Time Model saved successfully'))->send();

            // Refresh the form data
            $this->mount($model->id);
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }
    }

    #[Renderless]
    public function updateSchedule(int $weekIndex, int $dayNum, string $field, $value): void
    {
        if (! isset($this->workTimeModelForm->schedules[$weekIndex])) {
            $this->workTimeModelForm->schedules[$weekIndex] = [
                'week_number' => $weekIndex + 1,
                'days' => [],
            ];
        }

        if (! isset($this->workTimeModelForm->schedules[$weekIndex]['days'][$dayNum])) {
            $this->workTimeModelForm->schedules[$weekIndex]['days'][$dayNum] = [
                'weekday' => $dayNum,
                'start_time' => null,
                'end_time' => null,
                'work_hours' => 0,
                'break_minutes' => 0,
            ];
        }

        $this->workTimeModelForm->schedules[$weekIndex]['days'][$dayNum][$field] = $value;

        // Calculate work hours if start and end time are set
        if ($field === 'start_time' || $field === 'end_time') {
            $start = $this->workTimeModelForm->schedules[$weekIndex]['days'][$dayNum]['start_time'] ?? null;
            $end = $this->workTimeModelForm->schedules[$weekIndex]['days'][$dayNum]['end_time'] ?? null;

            if ($start && $end) {
                $startTime = \Carbon\Carbon::parse($start);
                $endTime = \Carbon\Carbon::parse($end);

                if ($endTime->lessThan($startTime)) {
                    $endTime->addDay();
                }

                $hours = $endTime->diffInMinutes($startTime) / 60;
                $this->workTimeModelForm->schedules[$weekIndex]['days'][$dayNum]['work_hours'] = round($hours, 2);
            } else {
                // If either start or end time is not set, set work_hours to 0
                $this->workTimeModelForm->schedules[$weekIndex]['days'][$dayNum]['work_hours'] = 0;
            }
        }

        // Ensure work_hours is always numeric
        if (! isset($this->workTimeModelForm->schedules[$weekIndex]['days'][$dayNum]['work_hours'])) {
            $this->workTimeModelForm->schedules[$weekIndex]['days'][$dayNum]['work_hours'] = 0;
        }
    }
}
