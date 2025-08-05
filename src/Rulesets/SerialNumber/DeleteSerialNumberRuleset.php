<?php

namespace FluxErp\Rulesets\SerialNumber;

use FluxErp\Models\SerialNumber;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteSerialNumberRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = SerialNumber::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => SerialNumber::class]),
            ],
        ];
    }
}
