<?php

namespace FluxErp\Rulesets\SalaryComponent;

use FluxErp\Models\SalaryComponent;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteSalaryComponentRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(SalaryComponent::class),
            ],
        ];
    }
}
