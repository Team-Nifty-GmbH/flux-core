<?php

namespace FluxErp\Actions\Employee;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Client;
use FluxErp\Models\Employee;
use FluxErp\Models\VacationCarryoverRule;
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
        $data = $this->getData();
        $workTimeModel = Arr::pull($data, 'work_time_model_id');

        $employee = app(Employee::class, ['attributes' => $data]);
        $employee->save();

        if ($workTimeModel) {
            AssignWorkTimeModel::make([
                'employee_id' => $employee->getKey(),
                'work_time_model_id' => $workTimeModel,
            ])
                ->validate()
                ->execute();
        }

        return $employee->refresh();
    }

    public function prepareForValidation(): void
    {
        $this->data['client_id'] ??= resolve_static(Client::class, 'default')
            ?->getKey();
        $this->data['vacation_carryover_rule_id'] ??= resolve_static(
            VacationCarryoverRule::class,
            'default'
        )
            ?->getKey();
    }
}
