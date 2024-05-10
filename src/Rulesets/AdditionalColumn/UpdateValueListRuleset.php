<?php

namespace FluxErp\Rulesets\AdditionalColumn;

use FluxErp\Models\AdditionalColumn;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateValueListRuleset extends FluxRuleset
{
    protected static ?string $model = AdditionalColumn::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                (new ModelExists(AdditionalColumn::class))->whereNotNull('values'),
            ],
            'name' => 'sometimes|required|string',
            'values' => 'sometimes|required|array',
        ];
    }
}
