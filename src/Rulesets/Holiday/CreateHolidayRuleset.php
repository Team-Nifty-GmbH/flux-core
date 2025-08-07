<?php

namespace FluxErp\Rulesets\Holiday;

use FluxErp\Models\Client;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rules\ModelExists;

class CreateHolidayRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'month' => 'required|integer|min:1|max:12',
            'day' => 'required|integer|min:1|max:31',
            'effective_from' => 'required|integer|min:2000|max:2100',
            'effective_until' => 'nullable|integer|min:2000|max:2100|gte:effective_from',
            'day_part' => 'required|in:full,first_half,second_half',
            'is_active' => 'boolean',
            'client_id' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
        ];
    }
}