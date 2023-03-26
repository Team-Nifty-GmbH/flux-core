<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\Iban;

class CreateBankConnectionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'contact_id' => 'integer|nullable|exists:contacts,id,deleted_at,NULL',
            'iban' => ['required', 'string', new Iban()],
            'account_holder' => 'sometimes|string|nullable',
            'bank_name' => 'sometimes|string|nullable',
            'bic' => 'sometimes|string|nullable',
        ];
    }
}
