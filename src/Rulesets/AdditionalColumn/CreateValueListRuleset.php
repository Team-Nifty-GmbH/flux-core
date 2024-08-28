<?php

namespace FluxErp\Rulesets\AdditionalColumn;

use FluxErp\Models\AdditionalColumn;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\HasAdditionalColumns;

class CreateValueListRuleset extends FluxRuleset
{
    protected static ?string $model = AdditionalColumn::class;

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'model_type' => [
                'required',
                'string',
                app(MorphClassExists::class, ['uses' => HasAdditionalColumns::class]),
            ],
            'values' => 'required|array',
        ];
    }
}
