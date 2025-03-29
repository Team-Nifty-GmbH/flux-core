<?php

namespace FluxErp\Rulesets\LanguageLine;

use FluxErp\Models\LanguageLine;
use FluxErp\Rules\ArrayIsKeyValuePair;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\UniqueInFieldDependence;
use FluxErp\Rulesets\FluxRuleset;

class UpdateLanguageLineRuleset extends FluxRuleset
{
    protected static ?string $model = LanguageLine::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LanguageLine::class]),
            ],
            'group' => 'sometimes|required|string|max:255',
            'key' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                app(
                    UniqueInFieldDependence::class,
                    [
                        'model' => LanguageLine::class,
                        'dependingField' => 'group',
                    ]
                ),
            ],
            'text' => [
                'sometimes',
                'required',
                app(ArrayIsKeyValuePair::class),
            ],
        ];
    }
}
