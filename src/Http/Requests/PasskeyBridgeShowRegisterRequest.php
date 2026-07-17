<?php

namespace FluxErp\Http\Requests;

class PasskeyBridgeShowRegisterRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'transfer_token' => ['required', 'string', 'size:64'],
        ];
    }
}
