<?php

namespace FluxErp\Rulesets\WorkTimeModel;

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateWorkTimeModelRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'cycle_weeks' => 'required|integer|min:1|max:52',
            'weekly_hours' => [
                'required',
                app(Numeric::class, ['min' => 0, 'max' => 168]),
            ],
            'annual_vacation_days' => [
                'required',
                app(Numeric::class, ['min' => 0, 'max' => 365]),
            ],
            'work_days_per_week' => 'nullable|integer|min:1|max:7',
            'max_overtime_hours' => [
                'nullable',
                app(Numeric::class, ['min' => 0]),
            ],
            'overtime_compensation' => [
                'required',
                Rule::enum(OvertimeCompensationEnum::class),
            ],
            'is_active' => 'boolean',
        ];
    }
}
