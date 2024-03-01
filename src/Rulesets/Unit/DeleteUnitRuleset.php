<?php

namespace FluxErp\Rulesets\Unit;

use FluxErp\Models\Unit;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteUnitRuleset extends FluxRuleset
{
    protected static ?string $model = Unit::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Unit::class),
            ],
        ];
    }
}
