<?php

namespace FluxErp\Actions\Employee;

use Carbon\Carbon;
use FluxErp\Actions\EmployeeWorkTimeModel\CreateEmployeeWorkTimeModel;
use FluxErp\Actions\EmployeeWorkTimeModel\UpdateEmployeeWorkTimeModel;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Rulesets\Employee\AssignWorkTimeModelRuleset;
use Illuminate\Validation\ValidationException;

class AssignWorkTimeModel extends FluxAction
{
    public ?EmployeeWorkTimeModel $currentWorkTimeModel = null;

    public static function models(): array
    {
        return [EmployeeWorkTimeModel::class];
    }

    protected function getRulesets(): string|array
    {
        return AssignWorkTimeModelRuleset::class;
    }

    public function performAction(): EmployeeWorkTimeModel
    {
        if ($this->currentWorkTimeModel) {
            UpdateEmployeeWorkTimeModel::make([
                'pivot_id' => $this->currentWorkTimeModel->getKey(),
                'valid_until' => $this->getData('valid_from')->copy()->subDay(),
            ])
                ->validate()
                ->execute();
        }

        return CreateEmployeeWorkTimeModel::make($this->getData())
            ->validate()
            ->execute();
    }

    public function validateData(): void
    {
        parent::validateData();

        $this->data['valid_from'] = Carbon::parse($this->getData('valid_from'));
        $this->currentWorkTimeModel = resolve_static(EmployeeWorkTimeModel::class, 'query')
            ->where('employee_id', $this->getData('employee_id'))
            ->whereNull('valid_until')
            ->first();

        if ($this->getData('valid_from')->lte($this->currentWorkTimeModel->valid_from)) {
            throw ValidationException::withMessages([
                'valid_from' => __('New assignment must start after the current assignment start date'),
            ]);
        }

        if ($this->getData('work_time_model_id') === $this->currentWorkTimeModel->work_time_model_id) {
            throw ValidationException::withMessages([
                'work_time_model_id' => __('The employee is already assigned to this work time model'),
            ]);
        }
    }
}
