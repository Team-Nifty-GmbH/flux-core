<?php

namespace FluxErp\Rulesets\WorkTimeModel;

use FluxErp\Models\WorkTimeModel;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateWorkTimeModelRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => WorkTimeModel::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'cycle_weeks' => 'sometimes|required|integer|min:1|max:52',
            'weekly_hours' => 'sometimes|required|numeric|min:0|max:168',
            'annual_vacation_days' => 'sometimes|required|numeric|min:0|max:365',
            'work_days_per_week' => 'nullable|integer|min:1|max:7',
            'max_overtime_hours' => 'nullable|numeric|min:0',
            'overtime_compensation' => 'sometimes|required|in:time_off,payment,both',
            'is_active' => 'boolean',
            'schedules' => 'nullable|array',
            'schedules.*.week_number' => 'required_with:schedules|integer|min:1|max:52',
            'schedules.*.days' => 'required_with:schedules|array',
        ];
    }
}
