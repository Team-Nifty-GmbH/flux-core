<?php

namespace FluxErp\Rulesets\Unit;

use FluxErp\Models\Unit;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateUnitRuleset extends FluxRuleset
{
    protected static ?string $model = Unit::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Unit::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'abbreviation' => 'sometimes|required|string|max:10',
        ];
    }
}
