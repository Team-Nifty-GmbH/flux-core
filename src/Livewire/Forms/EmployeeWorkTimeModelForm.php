<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\EmployeeWorkTimeModel\CreateEmployeeWorkTimeModel;
use FluxErp\Actions\EmployeeWorkTimeModel\DeleteEmployeeWorkTimeModel;
use FluxErp\Actions\EmployeeWorkTimeModel\UpdateEmployeeWorkTimeModel;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class EmployeeWorkTimeModelForm extends FluxForm
{
    use SupportsAutoRender;

    public ?float $annual_vacation_days = null;

    public ?int $employee_id = null;

    #[Locked]
    public ?int $id = null;

    public ?string $note = null;

    public ?string $valid_from = null;

    public ?string $valid_until = null;

    public ?int $work_time_model_id = null;

    public function fill($values): void
    {
        parent::fill($values);

        if ($values->valid_from) {
            $this->valid_from = $values->valid_from->format('Y-m-d');
        }
        if ($values->valid_until) {
            $this->valid_until = $values->valid_until->format('Y-m-d');
        }
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateEmployeeWorkTimeModel::class,
            'update' => UpdateEmployeeWorkTimeModel::class,
            'delete' => DeleteEmployeeWorkTimeModel::class,
        ];
    }

    protected function getCreateAction(): string
    {
        return CreateEmployeeWorkTimeModel::class;
    }

    protected function getDeleteAction(): string
    {
        return DeleteEmployeeWorkTimeModel::class;
    }

    protected function getModel(): string
    {
        return EmployeeWorkTimeModel::class;
    }

    protected function getUpdateAction(): string
    {
        return UpdateEmployeeWorkTimeModel::class;
    }
}
