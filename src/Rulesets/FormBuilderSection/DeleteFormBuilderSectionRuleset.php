<?php

namespace FluxErp\Rulesets\FormBuilderSection;

use FluxErp\Models\FormBuilderSection;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteFormBuilderSectionRuleset extends FluxRuleset
{
    protected static ?string $model = FormBuilderSection::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(FormBuilderSection::class),
            ],
        ];
    }
}
