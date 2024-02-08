<?php

namespace FluxErp\Http\Requests;

class CreateLanguageRequest extends BaseFormRequest
{
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
