<?php

namespace FluxErp\Rulesets\FormBuilderForm;

use FluxErp\Models\FormBuilderForm;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteFormBuilderFormRuleset extends FluxRuleset
{
    protected static ?string $model = FormBuilderForm::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(FormBuilderForm::class),
            ],
        ];
    }
}
