<?php

namespace FluxErp\Rulesets\AttributeTranslation;

use FluxErp\Models\Language;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\HasAttributeTranslations;

class CreateAttributeTranslationRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'language_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Language::class]),
            ],
            'model_type' => [
                'required',
                'string',
                app(MorphClassExists::class, ['uses' => HasAttributeTranslations::class]),
            ],
            'model_id' => [
                'required',
                'integer',
                app(MorphExists::class),
            ],
            'attribute' => [
                'required',
                'string',
                'max:255',
            ],
            'value' => [
                'required',
                'string',
            ],
        ];
    }
}
