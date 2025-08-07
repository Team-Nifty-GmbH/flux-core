<?php

namespace FluxErp\Rulesets\Holiday;

use FluxErp\Models\Client;
use FluxErp\Models\Holiday;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rules\ModelExists;

class UpdateHolidayRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Holiday::class),
            ],
            'name' => 'sometimes|required|string|max:255',
            'month' => 'sometimes|required|integer|min:1|max:12',
            'day' => 'sometimes|required|integer|min:1|max:31',
            'effective_from' => 'sometimes|required|integer|min:2000|max:2100',
            'effective_until' => 'nullable|integer|min:2000|max:2100|gte:effective_from',
            'day_part' => 'sometimes|required|in:full,first_half,second_half',
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