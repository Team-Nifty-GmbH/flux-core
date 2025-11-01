<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Employee\AssignWorkTimeModel;
use FluxErp\Actions\EmployeeWorkTimeModel\CreateEmployeeWorkTimeModel;
use FluxErp\Actions\EmployeeWorkTimeModel\DeleteEmployeeWorkTimeModel;
use FluxErp\Actions\EmployeeWorkTimeModel\UpdateEmployeeWorkTimeModel;
use FluxErp\Actions\FluxAction;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Livewire\Attributes\Locked;

class EmployeeWorkTimeModelForm extends FluxForm
{
    use SupportsAutoRender;

    public ?float $annual_vacation_days = null;

    public ?int $employee_id = null;

    public ?string $note = null;

    #[Locked]
    public ?int $pivot_id = null;

    public ?string $valid_from = null;

    public ?string $valid_until = null;

    public ?int $work_time_model_id = null;

    public function assign(): void
    {
        $action = $this->makeAction('assign')
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate();

        $response = $action->execute();

        $this->actionResult = $response;

        $this->fill($response);
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateEmployeeWorkTimeModel::class,
            'update' => UpdateEmployeeWorkTimeModel::class,
            'delete' => DeleteEmployeeWorkTimeModel::class,
            'assign' => AssignWorkTimeModel::class,
        ];
    }

    protected function getKey(): string
    {
        return 'pivot_id';
    }
}
