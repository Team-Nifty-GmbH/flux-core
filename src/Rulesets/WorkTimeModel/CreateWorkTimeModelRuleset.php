<?php

namespace FluxErp\Rulesets\WorkTimeModel;

use FluxErp\Rulesets\FluxRuleset;

class CreateWorkTimeModelRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'cycle_weeks' => 'required|integer|min:1|max:52',
            'weekly_hours' => 'required|numeric|min:0|max:168',
            'annual_vacation_days' => 'required|numeric|min:0|max:365',
            'work_days_per_week' => 'nullable|integer|min:1|max:7',
            'max_overtime_hours' => 'nullable|numeric|min:0',
            'overtime_compensation' => 'required|in:time_off,payment,both',
            'is_active' => 'boolean',
        ];
    }
}
