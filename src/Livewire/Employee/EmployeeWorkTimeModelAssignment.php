<?php

namespace FluxErp\Livewire\Employee;

use FluxErp\Livewire\Forms\EmployeeWorkTimeModelForm;
use FluxErp\Models\Employee;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class EmployeeWorkTimeModelAssignment extends Component
{
    use Actions;

    public array $assignments = [];

    public ?Employee $employee = null;

    public EmployeeWorkTimeModelForm $employeeWorkTimeModelForm;

    public function mount(?Employee $employee = null): void
    {
        $this->employee = $employee;
        if ($employee) {
            $this->loadAssignments();
        }
    }

    public function render()
    {
        $workTimeModels = resolve_static(WorkTimeModel::class, 'query')
            ->orderBy('name')
            ->get()
            ->map(fn ($model) => [
                'label' => $model->name,
                'value' => $model->id,
            ])
            ->toArray();

        return view('flux::livewire.human-resources.employee-work-time-model-assignment', [
            'workTimeModels' => $workTimeModels,
        ]);
    }

    public function assignWorkTimeModel(): bool
    {
        try {
            // Set employee_id in the form
            $this->employeeWorkTimeModelForm->employee_id = $this->employee->getKey();

            $validFrom = Carbon::parse($this->employeeWorkTimeModelForm->valid_from);

            // End current assignment if exists
            $currentAssignment = resolve_static(EmployeeWorkTimeModel::class, 'query')
                ->where('employee_id', $this->employee->getKey())
                ->whereNull('valid_until')
                ->first();

            if ($currentAssignment) {
                // Check if new assignment starts before current one
                if ($validFrom->lte($currentAssignment->valid_from)) {
                    $this->toast()
                        ->error(__('New assignment must start after the current assignment start date'))
                        ->send();

                    return false;
                }

                // End current assignment one day before new one starts
                $currentAssignment->update([
                    'valid_until' => $validFrom->copy()->subDay(),
                ]);
            }

            // Save using the form
            $this->employeeWorkTimeModelForm->save();

            // Also update the employee's current work_time_model_id for backward compatibility
            $this->employee->update([
                'work_time_model_id' => $this->employeeWorkTimeModelForm->work_time_model_id,
            ]);

            $this->toast()
                ->success(__('Work time model assigned successfully'))
                ->send();

            $this->loadAssignments();

            // Reset form
            $this->employeeWorkTimeModelForm->reset();

            // Dispatch event to refresh parent component
            $this->dispatch('workTimeModelAssigned', employeeId: $this->employee->getKey());

            return true;
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }
    }

    public function deleteAssignment(int $assignmentId): void
    {
        $assignment = resolve_static(EmployeeWorkTimeModel::class, 'query')->whereKey($assignmentId)->first();

        if (! $assignment || $assignment->employee_id !== $this->employee->getKey()) {
            $this->toast()
                ->error(__('Assignment not found'))
                ->send();

            return;
        }

        // Don't allow deleting current assignment
        if (! $assignment->valid_until) {
            $this->toast()
                ->error(__('Cannot delete current assignment. End it first.'))
                ->send();

            return;
        }

        $assignment->delete();

        $this->toast()
            ->success(__('Assignment deleted successfully'))
            ->send();

        $this->loadAssignments();
    }

    public function endAssignment(int $assignmentId): void
    {
        $assignment = resolve_static(EmployeeWorkTimeModel::class, 'query')->whereKey($assignmentId)->first();

        if (! $assignment || $assignment->employee_id !== $this->employee->getKey()) {
            $this->toast()
                ->error(__('Assignment not found'))
                ->send();

            return;
        }

        if ($assignment->valid_until) {
            $this->toast()
                ->error(__('This assignment has already ended'))
                ->send();

            return;
        }

        $assignment->update([
            'valid_until' => now(),
        ]);

        // Clear the employee's work_time_model_id if this was the current assignment
        $this->employee->update([
            'work_time_model_id' => null,
        ]);

        $this->toast()
            ->success(__('Assignment ended successfully'))
            ->send();

        $this->loadAssignments();

        // Dispatch event to refresh parent component
        $this->dispatch('workTimeModelAssigned', employeeId: $this->employee->getKey());
    }

    public function loadAssignments(): void
    {
        if (! $this->employee) {
            return;
        }

        $this->assignments = resolve_static(EmployeeWorkTimeModel::class, 'query')
            ->where('employee_id', $this->employee->getKey())
            ->with('workTimeModel')
            ->orderBy('valid_from', 'desc')
            ->get()
            ->map(fn ($assignment) => [
                'id' => $assignment->id,
                'work_time_model' => $assignment->workTimeModel?->name ?? __('Unknown'),
                'work_time_model_id' => $assignment->work_time_model_id,
                'valid_from' => $assignment->valid_from->format('Y-m-d'),
                'valid_until' => $assignment->valid_until?->format('Y-m-d'),
                'annual_vacation_days' => $assignment->annual_vacation_days ?? $assignment->workTimeModel?->annual_vacation_days,
                'note' => $assignment->note,
                'is_current' => ! $assignment->valid_until,
            ])
            ->toArray();
    }

    public function updatedEmployeeWorkTimeModelFormWorkTimeModelId(): void
    {
        if ($this->employeeWorkTimeModelForm->work_time_model_id) {
            $workTimeModel = resolve_static(WorkTimeModel::class, 'query')
                ->whereKey($this->employeeWorkTimeModelForm->work_time_model_id)
                ->first();

            if ($workTimeModel) {
                // Set default vacation days from work time model
                $this->employeeWorkTimeModelForm->annual_vacation_days = $workTimeModel->annual_vacation_days;
            }
        }
    }
}
