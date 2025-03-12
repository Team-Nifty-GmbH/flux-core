<?php

namespace FluxErp\Rulesets\AdditionalColumn;

use FluxErp\Models\AdditionalColumn;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteValueListRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = AdditionalColumn::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => AdditionalColumn::class])->whereNotNull('values'),
            ],
        ];
    }
}
