<?php

namespace FluxErp\Rulesets\Industry;

use FluxErp\Models\Industry;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteIndustryRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Industry::class]),
            ],
        ];
    }
}
