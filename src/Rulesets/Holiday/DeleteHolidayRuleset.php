<?php

namespace FluxErp\Rulesets\Holiday;

use FluxErp\Models\Holiday;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteHolidayRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Holiday::class]),
            ],
        ];
    }
}
