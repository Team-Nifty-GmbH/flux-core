<?php

namespace FluxErp\Rulesets\AdditionalColumn;

use FluxErp\Models\AdditionalColumn;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteValueListRuleset extends FluxRuleset
{
    protected static ?string $model = AdditionalColumn::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                (new ModelExists(AdditionalColumn::class))->whereNotNull('values'),
            ],
        ];
    }
}
