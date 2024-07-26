<?php

namespace FluxErp\Rulesets\Translation;

use FluxErp\Models\LanguageLine;
use FluxErp\Rules\ArrayIsKeyValuePair;
use FluxErp\Rules\UniqueInFieldDependence;
use FluxErp\Rulesets\FluxRuleset;

class CreateTranslationRuleset extends FluxRuleset
{
    protected static ?string $model = LanguageLine::class;

    public function rules(): array
    {
        return [
            'group' => 'required|string',
            'key' => [
                'required',
                'string',
                app(
                    UniqueInFieldDependence::class,
                    [
                        'model' => LanguageLine::class,
                        'dependingField' => 'group',
                        'ignoreSelf' => false,
                    ]
                ),
            ],
            'text' => [
                'required',
                app(ArrayIsKeyValuePair::class),
            ],
        ];
    }
}
