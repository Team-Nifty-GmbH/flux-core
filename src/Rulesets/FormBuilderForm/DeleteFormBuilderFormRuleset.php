<?php

namespace FluxErp\Rulesets\FormBuilderForm;

use FluxErp\Models\FormBuilderForm;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteFormBuilderFormRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = FormBuilderForm::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => FormBuilderForm::class]),
            ],
        ];
    }
}
