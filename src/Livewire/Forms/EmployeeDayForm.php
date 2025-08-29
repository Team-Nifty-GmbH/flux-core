<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\EmployeeDay\CreateEmployeeDay;
use FluxErp\Actions\EmployeeDay\DeleteEmployeeDay;
use FluxErp\Actions\EmployeeDay\UpdateEmployeeDay;
use FluxErp\Models\EmployeeDay;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class EmployeeDayForm extends FluxForm
{
    use SupportsAutoRender;

    #[Locked]
    public ?float $actual_hours = 0;

    #[Locked]
    public ?float $break_minutes = 0;

    #[Locked]
    public ?string $date = null;

    public ?array $employee = null;

    #[Locked]
    public ?int $employee_id = null;

    #[Locked]
    public ?int $id = null;

    public ?float $plus_minus_overtime_hours = 0;

    public ?float $plus_minus_vacation_hours = 0;

    #[Locked]
    public ?float $sick_days_used = 0;

    #[Locked]
    public ?float $sick_hours_used = 0;

    public ?float $target_hours = 0;

    #[Locked]
    public ?float $vacation_days_used = 0;

    #[Locked]
    public ?float $vacation_hours_used = 0;

    public function fill($values): void
    {
        if ($values instanceof EmployeeDay) {
            $values->loadMissing([
                'employee:id,name,firstname,lastname',
                'workTimes' => function ($q): void {
                    $q->where('is_daily_work_time', true)
                        ->orderBy('started_at');
                },
                'absenceRequests.absenceType:id,name,color',
            ]);
        }

        parent::fill($values);

        if ($values->date) {
            $this->date = $values->date->format('Y-m-d');
        }
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateEmployeeDay::class,
            'update' => UpdateEmployeeDay::class,
            'delete' => DeleteEmployeeDay::class,
        ];
    }
}
