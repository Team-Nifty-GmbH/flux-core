<?php

namespace FluxErp\Rulesets\AdditionalColumn;

use FluxErp\Models\AdditionalColumn;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteAdditionalColumnRuleset extends FluxRuleset
{
    protected static ?string $model = AdditionalColumn::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => AdditionalColumn::class]),
            ],
        ];
    }
}
