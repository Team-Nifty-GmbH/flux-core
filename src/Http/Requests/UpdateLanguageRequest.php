<?php

namespace FluxErp\Http\Requests;

class UpdateLanguageRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:languages,id,deleted_at,NULL',
            'name' => 'string',
            'iso_name' => 'string|unique:languages,iso_name',
            'language_code' => 'string|unique:languages,language_code',
            'is_default' => 'boolean',
        ];
    }
}
