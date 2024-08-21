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
            'name' => 'string',
            'iso_name' => 'string|unique:languages,iso_name',
            'language_code' => 'string|unique:languages,language_code',
            'is_default' => 'boolean',
        ];
    }
}
