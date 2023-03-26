<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;
use FluxErp\Rules\Iban;

class UpdateAccountRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:accounts,id,deleted_at,NULL',
            'bank_connection_id' => [
                'integer',
                'nullable',
                (new ExistsWithIgnore('bank_connections', 'id'))->whereNull('deleted_at'),
            ],
            'currency_id' => [
                'integer',
                'nullable',
                (new ExistsWithIgnore('currencies', 'id'))->whereNull('deleted_at'),
            ],
            'name' => 'string|nullable',
            'account_number' => 'sometimes|required|string',
            'account_holder' => 'string|nullable',
            'iban' => [
                'string',
                'nullable',
                new Iban(),
            ],
            'type' => 'string|nullable',
        ];
    }
}
