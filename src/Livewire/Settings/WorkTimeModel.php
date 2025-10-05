<?php

namespace FluxErp\Livewire\Settings;

use Carbon\Carbon;
use Exception;
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
            ->whereKey($id)
            ->with('schedules')
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
            $this->workTimeModelForm->delete();

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
        $cycleWeeks = (int) $this->workTimeModelForm->cycle_weeks ?: 1;

        if ($cycleWeeks > $currentWeeks) {
            // Add new weeks
            for ($weekNumber = $currentWeeks + 1; $weekNumber <= $cycleWeeks; $weekNumber++) {
                // Copy first week's schedule as template
                $template = $this->workTimeModelForm->schedules[0] ?? null;
                if ($template) {
                    $newWeek = $template;
                    $newWeek['week_number'] = $weekNumber;
                    $this->workTimeModelForm->schedules[] = $newWeek;
                } else {
                    // Create default week
                    $this->workTimeModelForm->schedules[] = $this->workTimeModelForm
                        ->getDefaultWeekSchedule($weekNumber);
                }
            }
        } elseif ($cycleWeeks < $currentWeeks) {
            // Remove extra weeks
            $this->workTimeModelForm->schedules = array_slice($this->workTimeModelForm->schedules, 0, $cycleWeeks);
        }
    }

    public function save(): void
    {
        try {
            $this->workTimeModelForm->save();
            $this->workTimeModelForm->loadSchedules($this->workTimeModelForm->getActionResult());
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->toast()
            ->success(__(':model saved', ['model' => __('Work Time Model')]))
            ->send();
    }

    #[Renderless]
    public function updateSchedule(int $weekIndex, int $dayNumber, string $property, $value): void
    {
        if (! data_get($this->workTimeModelForm->schedules, $weekIndex)) {
            $this->workTimeModelForm->schedules[$weekIndex] = [
                'week_number' => $weekIndex + 1,
                'days' => [],
            ];
        }

        if (! data_get($this->workTimeModelForm->schedules, $weekIndex . '.days.' . $dayNumber)) {
            $this->workTimeModelForm->schedules[$weekIndex]['days'][$dayNumber] = [
                'weekday' => $dayNumber,
                'start_time' => null,
                'end_time' => null,
                'work_hours' => 0,
                'break_minutes' => 0,
            ];
        }

        $this->workTimeModelForm->schedules[$weekIndex]['days'][$dayNumber][$property] = $value;

        // Calculate work hours if start and end time are set
        if ($property === 'start_time' || $property === 'end_time') {
            $start = $this->workTimeModelForm->schedules[$weekIndex]['days'][$dayNumber]['start_time'] ?? null;
            $end = $this->workTimeModelForm->schedules[$weekIndex]['days'][$dayNumber]['end_time'] ?? null;

            if ($start && $end) {
                $startTime = Carbon::parse($start);
                $endTime = Carbon::parse($end);

                if ($endTime->lessThan($startTime)) {
                    $endTime->addDay();
                }

                $hours = $endTime->diffInMinutes($startTime) / 60;
                $this->workTimeModelForm->schedules[$weekIndex]['days'][$dayNumber]['work_hours'] = bcround($hours, 2);
            } else {
                // If either start or end time is not set, set work_hours to 0
                $this->workTimeModelForm->schedules[$weekIndex]['days'][$dayNumber]['work_hours'] = 0;
            }
        }

        // Ensure work_hours is always numeric
        if (! data_get($this->workTimeModelForm->schedules, $weekIndex . '.days.' . $dayNumber . '.work_hours')) {
            $this->workTimeModelForm->schedules[$weekIndex]['days'][$dayNumber]['work_hours'] = 0;
        }
    }
}
