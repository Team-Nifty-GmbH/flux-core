<?php

namespace FluxErp\Http\Requests;

class CreateLanguageRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
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
