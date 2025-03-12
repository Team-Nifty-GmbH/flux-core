<?php

namespace FluxErp\Rulesets\FormBuilderSection;

use FluxErp\Models\FormBuilderSection;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteFormBuilderSectionRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = FormBuilderSection::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => FormBuilderSection::class]),
            ],
        ];
    }
}
