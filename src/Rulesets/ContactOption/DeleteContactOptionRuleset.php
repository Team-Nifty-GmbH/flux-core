<?php

namespace FluxErp\Rulesets\ContactOption;

use FluxErp\Models\ContactOption;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteContactOptionRuleset extends FluxRuleset
{
    protected static ?string $model = ContactOption::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ContactOption::class]),
            ],
        ];
    }
}
