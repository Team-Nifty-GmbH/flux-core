<?php

namespace FluxErp\Rulesets\SepaMandate;

use FluxErp\Models\SepaMandate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteSepaMandateRuleset extends FluxRuleset
{
    protected static ?string $model = SepaMandate::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(SepaMandate::class),
            ],
        ];
    }
}
