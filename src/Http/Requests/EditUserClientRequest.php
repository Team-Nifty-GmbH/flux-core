<?php

namespace FluxErp\Http\Requests;

class EditUserClientRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id,deleted_at,NULL',
            'clients' => 'present|array',
            'clients.*' => 'required|integer|exists:clients,id,deleted_at,NULL',
        ];
    }
}
