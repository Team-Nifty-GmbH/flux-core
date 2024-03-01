<?php

namespace FluxErp\Rulesets\FormBuilderField;

use FluxErp\Models\FormBuilderField;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteFormBuilderFieldRuleset extends FluxRuleset
{
    protected static ?string $model = FormBuilderField::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(FormBuilderField::class),
            ],
        ];
    }
}
