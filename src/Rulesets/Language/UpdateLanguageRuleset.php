<?php

namespace FluxErp\Rulesets\Language;

use FluxErp\Models\Language;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateLanguageRuleset extends FluxRuleset
{
    protected static ?string $model = Language::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Language::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'iso_name' => 'sometimes|required|string|max:255|unique:languages,iso_name',
            'language_code' => 'sometimes|required|string|max:255|unique:languages,language_code',
            'is_default' => 'boolean',
        ];
    }
}
