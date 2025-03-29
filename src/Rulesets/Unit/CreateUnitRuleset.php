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
            'uuid' => 'nullable|string|uuid|unique:units,uuid',
            'name' => 'required|string|max:255',
            'abbreviation' => 'required|string|max:10',
        ];
    }
}
