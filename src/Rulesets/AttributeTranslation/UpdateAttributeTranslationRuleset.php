<?php

namespace FluxErp\Rulesets\AttributeTranslation;

use FluxErp\Models\AttributeTranslation;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateAttributeTranslationRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => AttributeTranslation::class]),
            ],
            'value' => [
                'required',
                'string',
            ],
        ];
    }
}
