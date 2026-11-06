<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Employee\UpdateEmployee;
use Livewire\Attributes\Locked;

class EmployeeSalaryForm extends FluxForm
{
    public ?string $base_salary = null;

    public ?string $employer_contribution_rate = null;

    public ?string $hourly_rate = null;

    #[Locked]
    public ?int $id = null;

    protected function getActions(): array
    {
        return [
            'update' => UpdateEmployee::class,
        ];
    }
}
