<?php

namespace FluxErp\Rulesets\Holiday;

use FluxErp\Models\Holiday;
use FluxErp\Models\Location;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateHolidayRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Holiday::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'date' => 'nullable|date',
            'month' => 'nullable|integer|min:1|max:12',
            'day' => 'nullable|integer|min:1|max:31',
            'year' => 'nullable|integer|min:2000|max:2100',
            'is_recurring' => 'boolean',
            'is_half_day' => 'boolean',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after_or_equal:effective_from',
            'is_active' => 'boolean',
            'location_ids' => 'nullable|array',
            'location_ids.*' => [
                'integer',
                app(ModelExists::class, ['model' => Location::class]),
            ],
        ];
    }
}
