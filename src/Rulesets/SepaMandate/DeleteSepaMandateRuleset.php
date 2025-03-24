<?php

namespace FluxErp\Rulesets\SepaMandate;

use FluxErp\Models\SepaMandate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteSepaMandateRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = SepaMandate::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => SepaMandate::class]),
            ],
        ];
    }
}
