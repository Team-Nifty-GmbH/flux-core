<?php

namespace FluxErp\Rulesets\FormBuilderField;

use FluxErp\Models\FormBuilderField;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteFormBuilderFieldRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = FormBuilderField::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => FormBuilderField::class]),
            ],
        ];
    }
}
