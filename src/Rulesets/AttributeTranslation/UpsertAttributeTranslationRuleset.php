<?php

namespace FluxErp\Rulesets\AttributeTranslation;

use FluxErp\Models\AttributeTranslation;
use FluxErp\Models\Language;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rules\Translatable;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\HasAttributeTranslations;

class UpsertAttributeTranslationRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => AttributeTranslation::class]),
            ],
            'language_id' => [
                'exclude_with:id',
                'required',
                'integer',
                app(ModelExists::class, ['model' => Language::class]),
            ],
            'model_type' => [
                'exclude_with:id',
                'required',
                'string',
                app(MorphClassExists::class, ['uses' => HasAttributeTranslations::class]),
            ],
            'model_id' => [
                'exclude_with:id',
                'required',
                'integer',
                app(MorphExists::class),
            ],
            'attribute' => [
                'exclude_with:id',
                'required',
                'string',
                app(Translatable::class),
            ],
            'value' => [
                'required',
                'string',
            ],
        ];
    }
}
