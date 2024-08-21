<?php

namespace FluxErp\Rulesets\SerialNumberRange;

use FluxErp\Models\SerialNumberRange;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteSerialNumberRangeRuleset extends FluxRuleset
{
    protected static ?string $model = SerialNumberRange::class;

    protected static bool $addAdditionalColumnRules = false;

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
