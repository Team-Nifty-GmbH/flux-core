<?php

namespace FluxErp\Rulesets\SerialNumberRange;

use FluxErp\Models\SerialNumberRange;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteSerialNumberRangeRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = SerialNumberRange::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => SerialNumberRange::class]),
            ],
        ];
    }
}
