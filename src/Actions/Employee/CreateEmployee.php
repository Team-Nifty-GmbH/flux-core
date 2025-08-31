<?php

namespace FluxErp\Actions\Employee;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Client;
use FluxErp\Models\Employee;
use FluxErp\Models\VacationCarryOverRule;
use FluxErp\Rulesets\Employee\CreateEmployeeRuleset;
use Illuminate\Support\Arr;

class CreateEmployee extends FluxAction
{
    public static function models(): array
    {
        return [Employee::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateEmployeeRuleset::class;
    }

    public function performAction(): Employee
    {
        $workTimeModel = Arr::pull($this->data, 'work_time_model_id');

        $employee = app(Employee::class, ['attributes' => $this->getData()]);
        $employee->save();

        if ($workTimeModel) {
            AssignWorkTimeModel::make([
                'employee_id' => $employee->getKey(),
                'work_time_model_id' => $workTimeModel,
            ])
                ->validate()
                ->execute();
        }

        return $employee->fresh();
    }

    public function prepareForValidation(): void
    {
        $this->data['client_id'] ??= resolve_static(Client::class, 'default')->getKey();
        $this->data['vacation_carry_over_rule_id'] ??= resolve_static(
            VacationCarryOverRule::class,
            'default'
        )
            ?->getKey();
    }
}
