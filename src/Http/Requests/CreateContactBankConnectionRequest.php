<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\Iban;

class CreateContactBankConnectionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:bank_connections,uuid',
            'contact_id' => 'integer|nullable|exists:contacts,id,deleted_at,NULL',
            'iban' => ['required', 'string', new Iban()],
            'account_holder' => 'sometimes|string|nullable',
            'bank_name' => 'sometimes|string|nullable',
            'bic' => 'sometimes|string|nullable',
        ];
    }
}
