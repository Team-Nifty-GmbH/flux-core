<?php

namespace FluxErp\Rulesets\Translation;

use FluxErp\Models\LanguageLine;
use FluxErp\Rules\ArrayIsKeyValuePair;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\UniqueInFieldDependence;
use FluxErp\Rulesets\FluxRuleset;

class UpdateTranslationRuleset extends FluxRuleset
{
    protected static ?string $model = LanguageLine::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(LanguageLine::class),
            ],
            'group' => 'sometimes|required|string',
            'key' => [
                'sometimes',
                'required',
                'string',
                new UniqueInFieldDependence(LanguageLine::class, 'group'),
            ],
            'text' => [
                'sometimes',
                'required',
                new ArrayIsKeyValuePair(),
            ],
        ];
    }
}
