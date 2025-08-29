<?php

namespace FluxErp\Rulesets\VacationBlackout;

use FluxErp\Rulesets\FluxRuleset;

class CreateVacationBlackoutRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}
