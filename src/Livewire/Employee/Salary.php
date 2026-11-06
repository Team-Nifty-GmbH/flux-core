<?php

namespace FluxErp\Livewire\Employee;

use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\DataTables\SalaryComponentList;
use FluxErp\Livewire\Forms\EmployeeSalaryForm;
use FluxErp\Livewire\Forms\SalaryComponentForm;
use FluxErp\Models\Employee;
use FluxErp\Support\Livewire\Attributes\DataTableForm;
use FluxErp\Traits\Livewire\DataTable\DataTableHasFormEdit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Salary extends SalaryComponentList
{
    use DataTableHasFormEdit {
        DataTableHasFormEdit::delete as baseDelete;
        DataTableHasFormEdit::save as baseSave;
    }

    #[Locked]
    public array $costs = [];

    #[Modelable]
    public ?int $employeeId = null;

    public EmployeeSalaryForm $employeeSalaryForm;

    public ?string $includeBefore = 'flux::livewire.employee.salary';

    #[DataTableForm]
    public SalaryComponentForm $salaryComponentForm;

    public function mount(): void
    {
        resolve_static(TabButton::class, 'make', ['component' => 'employee.salary'])
            ->userHasTabPermission();

        parent::mount();

        $employee = resolve_static(Employee::class, 'query')
            ->whereKey($this->employeeId)
            ->firstOrFail();

        $this->employeeSalaryForm->fill(
            $employee->makeVisible(['base_salary', 'employer_contribution_rate', 'hourly_rate'])
        );

        $this->calculateCosts($employee);
    }

    #[Renderless]
    public function delete(string|int $id): bool
    {
        $deleted = $this->baseDelete($id);
        $this->calculateCosts();

        return $deleted;
    }

    #[Renderless]
    public function save(): bool
    {
        $this->salaryComponentForm->employee_id = $this->employeeId;

        $saved = $this->baseSave();
        $this->calculateCosts();

        return $saved;
    }

    #[Renderless]
    public function saveSalary(): bool
    {
        try {
            $this->employeeSalaryForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this, form: $this->employeeSalaryForm);

            return false;
        }

        $this->calculateCosts();
        $this->toast()
            ->success(__(':model saved', ['model' => __('Salary')]))
            ->send();

        return true;
    }

    protected function calculateCosts(?Employee $employee = null): void
    {
        $employee ??= resolve_static(Employee::class, 'query')
            ->whereKey($this->employeeId)
            ->firstOrFail();

        $this->costs = array_map(
            fn (string $value): string => Number::currency((float) $value),
            $employee->getMonthlyPersonnelCosts()
        );
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->where('employee_id', $this->employeeId);
    }
}
