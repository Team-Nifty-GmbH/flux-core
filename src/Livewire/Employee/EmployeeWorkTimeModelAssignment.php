<?php

namespace FluxErp\Livewire\Employee;

use FluxErp\Livewire\Forms\EmployeeWorkTimeModelForm;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class EmployeeWorkTimeModelAssignment extends Component
{
    use Actions;

    #[Locked]
    public array $assignments = [];

    #[Locked]
    public int $employeeId;

    public EmployeeWorkTimeModelForm $employeeWorkTimeModelForm;

    public function mount(): void
    {
        $this->loadAssignments();
    }

    public function render(): View
    {
        return view('flux::livewire.employee.employee-work-time-model-assignment');
    }

    public function assignWorkTimeModel(): bool
    {
        try {
            $this->employeeWorkTimeModelForm->employee_id = $this->employeeId;
            $this->employeeWorkTimeModelForm->assign();

            $this->toast()
                ->success(__('Work time model assigned successfully'))
                ->send();

            $this->loadAssignments();

            $this->employeeWorkTimeModelForm->reset();

            return true;
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }
    }

    public function loadAssignments(): void
    {
        $this->assignments = resolve_static(EmployeeWorkTimeModel::class, 'query')
            ->where('employee_id', $this->employeeId)
            ->with('workTimeModel:id,name,annual_vacation_days')
            ->orderBy('valid_from', 'desc')
            ->get()
            ->map(fn ($assignment) => [
                'id' => $assignment->id,
                'work_time_model' => $assignment->workTimeModel?->name ?? __('Unknown'),
                'work_time_model_id' => $assignment->work_time_model_id,
                'valid_from' => $assignment->valid_from->format('Y-m-d'),
                'valid_until' => $assignment->valid_until?->format('Y-m-d'),
                'annual_vacation_days' => $assignment->annual_vacation_days
                    ?? $assignment->workTimeModel?->annual_vacation_days,
                'note' => $assignment->note,
                'is_current' => ! $assignment->valid_until,
            ])
            ->toArray();
    }

    public function updatedEmployeeWorkTimeModelFormWorkTimeModelId(): void
    {
        $this->skipRender();

        if (! $this->employeeWorkTimeModelForm->work_time_model_id) {
            return;
        }

        $this->employeeWorkTimeModelForm->annual_vacation_days = resolve_static(WorkTimeModel::class, 'query')
            ->whereKey($this->employeeWorkTimeModelForm->work_time_model_id)
            ->value('annual_vacation_days');
    }
}
