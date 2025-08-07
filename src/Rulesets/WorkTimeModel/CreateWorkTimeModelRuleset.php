<?php

namespace FluxErp\Rulesets\WorkTimeModel;

use FluxErp\Models\Client;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rules\ModelExists;

class CreateWorkTimeModelRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'cycle_weeks' => 'required|integer|min:1|max:52',
            'weekly_hours' => 'required|numeric|min:0|max:168',
            'annual_vacation_days' => 'required|integer|min:0|max:365',
            'max_overtime_hours' => 'nullable|numeric|min:0',
            'overtime_compensation' => 'required|in:time_off,payment,both',
            'has_core_hours' => 'boolean',
            'core_hours_start' => 'required_if:has_core_hours,true|nullable|date_format:H:i',
            'core_hours_end' => 'required_if:has_core_hours,true|nullable|date_format:H:i|after:core_hours_start',
            'is_active' => 'boolean',
            'client_id' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
        ];
    }
}