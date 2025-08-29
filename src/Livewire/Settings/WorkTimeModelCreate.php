<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;
use FluxErp\Livewire\Forms\WorkTimeModelForm;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class WorkTimeModelCreate extends Component
{
    use Actions;

    public WorkTimeModelForm $workTimeModelForm;

    public function mount(): void
    {
        $this->workTimeModelForm->initializeDefaultSchedules();
    }

    public function render(): View
    {
        return view('flux::livewire.settings.work-time-model');
    }

    public function save(): void
    {
        try {
            // Store schedules temporarily
            $schedules = $this->workTimeModelForm->schedules;

            // Get form data without schedules
            $data = $this->workTimeModelForm->toArray();
            unset($data['schedules']);

            // Create the work time model (without schedules)
            $model = CreateWorkTimeModel::make($data)
                ->checkPermission()
                ->validate()
                ->execute();

            // Save schedules
            if ($model && ! empty($schedules)) {
                foreach ($schedules as $week) {
                    foreach ($week['days'] as $day => $dayData) {
                        if ($dayData) {
                            \FluxErp\Models\WorkTimeModelSchedule::create([
                                'work_time_model_id' => $model->id,
                                'week_number' => $week['week_number'],
                                'weekday' => $dayData['weekday'],
                                'start_time' => $dayData['start_time'],
                                'end_time' => $dayData['end_time'],
                                'work_hours' => abs($dayData['work_hours'] ?? 0),
                                'break_minutes' => abs($dayData['break_minutes'] ?? 0),
                            ]);
                        }
                    }
                }
            }

            $this->toast()->success(__('Work Time Model created successfully'))->send();
            $this->redirect(route('settings.work-time-model', $model->id));
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
            }
        }
    }
}
