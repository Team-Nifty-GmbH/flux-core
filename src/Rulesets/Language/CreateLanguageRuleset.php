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
            'uuid' => 'nullable|string|uuid|unique:languages,uuid',
            'name' => 'required|string|max:255',
            'iso_name' => 'required|string|max:255',
            'language_code' => 'required|string|max:255|unique:languages,language_code',
            'is_default' => 'boolean',
        ];
    }
}
