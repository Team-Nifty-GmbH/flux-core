<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Language;
use FluxErp\Rules\ModelExists;

class UpdateLanguageRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Language::class),
            ],
            'name' => 'string',
            'iso_name' => 'string|unique:languages,iso_name',
            'language_code' => 'string|unique:languages,language_code',
            'is_default' => 'boolean',
        ];
    }
}
