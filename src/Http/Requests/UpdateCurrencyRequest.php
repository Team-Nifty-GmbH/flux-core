<?php

namespace FluxErp\Http\Requests;

class UpdateCurrencyRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:currencies,id,deleted_at,NULL',
            'name' => 'string',
            'iso' => 'string|unique:currencies,iso',
            'symbol' => 'string',
            'is_default' => 'boolean',
        ];
    }
}
