<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\Iban;

class CreateAccountRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:accounts,uuid',
            'bank_connection_id' => 'integer|nullable|exists:bank_connections,id,deleted_at,NULL',
            'currency_id' => 'integer|nullable|exists:currencies,id,deleted_at,NULL',
            'name' => 'string|nullable',
            'account_number' => 'required|string',
            'account_holder' => 'string|nullable',
            'iban' => [
                'required_without:bank_connection_id',
                'string',
                new Iban(),
            ],
            'type' => 'string|nullable',
        ];
    }
}
