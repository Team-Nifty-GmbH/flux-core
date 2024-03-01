<?php

namespace FluxErp\Rulesets\Unit;

use FluxErp\Models\Unit;
use FluxErp\Rulesets\FluxRuleset;

class CreateUnitRuleset extends FluxRuleset
{
    protected static ?string $model = Unit::class;

    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:units,uuid',
            'name' => 'required|string',
            'abbreviation' => 'required|string',
        ];
    }
}
