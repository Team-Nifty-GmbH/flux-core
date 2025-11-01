<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\EmployeeBalanceAdjustment\CreateEmployeeBalanceAdjustment;
use FluxErp\Actions\EmployeeBalanceAdjustment\DeleteEmployeeBalanceAdjustment;
use FluxErp\Actions\EmployeeBalanceAdjustment\UpdateEmployeeBalanceAdjustment;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class EmployeeBalanceAdjustmentForm extends FluxForm
{
    use SupportsAutoRender;

    public ?string $amount = null;

    public ?string $description = null;

    public ?string $effective_date = null;

    public ?int $employee_id = null;

    #[Locked]
    public ?int $id = null;

    public ?string $reason = null;

    public ?string $type = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateEmployeeBalanceAdjustment::class,
            'update' => UpdateEmployeeBalanceAdjustment::class,
            'delete' => DeleteEmployeeBalanceAdjustment::class,
        ];
    }
}
