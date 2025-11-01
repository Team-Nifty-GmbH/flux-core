<?php

namespace FluxErp\Rulesets\Employee;

use FluxErp\Models\Employee;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteEmployeeRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Employee::class]),
            ],
        ];
    }
}
