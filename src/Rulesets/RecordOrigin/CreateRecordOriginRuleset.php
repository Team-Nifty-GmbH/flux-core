<?php

namespace FluxErp\Rulesets\RecordOrigin;

use FluxErp\Models\RecordOrigin;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateRecordOriginRuleset extends FluxRuleset
{
    protected static ?string $model = RecordOrigin::class;

    public function rules(): array
    {
        return [
            'model_type' => [
                'required',
                'string',
                'max:255',
                app(MorphClassExists::class),
            ],
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
