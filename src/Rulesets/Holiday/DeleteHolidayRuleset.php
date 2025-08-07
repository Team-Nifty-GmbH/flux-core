<?php

namespace FluxErp\Rulesets\Holiday;

use FluxErp\Models\Holiday;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rules\ModelExists;

class DeleteHolidayRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Holiday::class),
            ],
        ];
    }
}