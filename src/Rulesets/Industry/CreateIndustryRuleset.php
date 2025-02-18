<?php

namespace FluxErp\Rulesets\Industry;

use FluxErp\Rulesets\FluxRuleset;

class CreateIndustryRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
