<?php

namespace FluxErp\Rulesets\SerialNumber;

use FluxErp\Models\SerialNumber;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteSerialNumberRuleset extends FluxRuleset
{
    protected static ?string $model = SerialNumber::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(SerialNumber::class),
            ],
        ];
    }
}
