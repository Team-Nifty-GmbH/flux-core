<?php

namespace FluxErp\Rulesets\Industry;

use FluxErp\Models\Industry;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateIndustryRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Industry::class]),
            ],
            'name' => 'required|string|max:255',
        ];
    }
}
