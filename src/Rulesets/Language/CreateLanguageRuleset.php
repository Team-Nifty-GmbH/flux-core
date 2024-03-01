<?php

namespace FluxErp\Rulesets\Language;

use FluxErp\Models\Language;
use FluxErp\Rulesets\FluxRuleset;

class CreateLanguageRuleset extends FluxRuleset
{
    protected static ?string $model = Language::class;

    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:languages,uuid',
            'name' => 'required|string',
            'iso_name' => 'required|string',
            'language_code' => 'required|string|unique:languages,language_code',
            'is_default' => 'boolean',
        ];
    }
}
