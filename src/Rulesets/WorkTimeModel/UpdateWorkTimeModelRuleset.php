<?php

namespace FluxErp\Rulesets\WorkTimeModel;

use FluxErp\Enums\OvertimeCompensationEnum;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

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
            'weekly_hours' => [
                'sometimes',
                'required',
                app(Numeric::class, ['min' => 0, 'max' => 168]),
            ],
            'annual_vacation_days' => [
                'sometimes',
                'required',
                app(Numeric::class, ['min' => 0, 'max' => 365]),
            ],
            'work_days_per_week' => 'nullable|integer|min:1|max:7',
            'max_overtime_hours' => [
                'nullable',
                app(Numeric::class, ['min' => 0]),
            ],
            'overtime_compensation' => [
                'sometimes',
                'required',
                Rule::enum(OvertimeCompensationEnum::class),
            ],
            'is_active' => 'boolean',

            'schedules' => 'nullable|array',
            'schedules.*.week_number' => 'required|integer|min:1|max:52',
            'schedules.*.days' => 'required|array',
            'schedules.*.days.*.weekday' => 'required|integer|min:0|max:6',
            'schedules.*.days.*.start_time' => [
                'nullable',
                Rule::anyOf(['date_format:H:i', 'date_format:H:i:s']),
            ],
            'schedules.*.days.*.end_time' => [
                'nullable',
                Rule::anyOf(['date_format:H:i', 'date_format:H:i:s']),
            ],
            'schedules.*.days.*.break_minutes' => 'nullable|integer|min:0|max:1440',
            'schedules.*.days.*.work_hours' => [
                'nullable',
                app(Numeric::class, ['min' => 0, 'max' => 24]),
            ],
        ];
    }
}
