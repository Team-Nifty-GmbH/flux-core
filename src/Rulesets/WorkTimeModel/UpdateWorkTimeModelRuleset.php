<?php

namespace FluxErp\Rulesets\WorkTimeModel;

use FluxErp\Models\Client;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rules\ModelExists;

class UpdateWorkTimeModelRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(WorkTimeModel::class),
            ],
            'name' => 'sometimes|required|string|max:255',
            'cycle_weeks' => 'sometimes|required|integer|min:1|max:52',
            'weekly_hours' => 'sometimes|required|numeric|min:0|max:168',
            'annual_vacation_days' => 'sometimes|required|integer|min:0|max:365',
            'max_overtime_hours' => 'nullable|numeric|min:0',
            'overtime_compensation' => 'sometimes|required|in:time_off,payment,both',
            'has_core_hours' => 'boolean',
            'core_hours_start' => 'required_if:has_core_hours,true|nullable|date_format:H:i',
            'core_hours_end' => 'required_if:has_core_hours,true|nullable|date_format:H:i|after:core_hours_start',
            'is_active' => 'boolean',
            'client_id' => [
                'sometimes',
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
        ];
    }
}