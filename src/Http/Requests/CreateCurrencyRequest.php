<?php

namespace FluxErp\Http\Requests;

class CreateCurrencyRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:currencies,uuid',
            'name' => 'required|string',
            'iso' => 'required|string|unique:currencies,iso',
            'symbol' => 'required|string',
            'is_default' => 'boolean',
        ];
    }
}
