<?php

namespace FluxErp\Rulesets\WorkTimeModel;

use FluxErp\Models\WorkTimeModel;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rules\ModelExists;

class DeleteWorkTimeModelRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(WorkTimeModel::class),
            ],
        ];
    }
}