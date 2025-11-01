<?php

namespace FluxErp\Rulesets\Holiday;

use FluxErp\Enums\DayPartEnum;
use FluxErp\Models\Location;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateHolidayRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'date' => 'required_if_declined:is_recurring|nullable|date',
            'effective_from' => 'nullable|integer|min:1970',
            'effective_until' => 'nullable|integer|gte:effective_from',
            'month' => [
                'required_if_accepted:is_recurring',
                'nullable',
                'integer',
                'min:1',
                'max:12',
            ],
            'day' => [
                'required_if_accepted:is_recurring',
                'nullable',
                'integer',
                'min:1',
                'max:31',
            ],
            'day_part' => [
                'required',
                Rule::enum(DayPartEnum::class),
            ],
            'is_active' => 'boolean',
            'is_recurring' => 'boolean',

            'locations' => 'nullable|array',
            'locations.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Location::class]),
            ],
        ];
    }
}
