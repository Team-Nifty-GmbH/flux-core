<?php

namespace FluxErp\Rulesets\RecordOrigin;

use FluxErp\Models\RecordOrigin;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateRecordOriginRuleset extends FluxRuleset
{
    protected static ?string $model = RecordOrigin::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => RecordOrigin::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
