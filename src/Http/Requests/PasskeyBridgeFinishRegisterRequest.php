<?php

namespace FluxErp\Http\Requests;

class PasskeyBridgeFinishRegisterRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:64'],
            'transfer_token' => ['required', 'string', 'size:64'],
            'name' => ['required', 'string', 'max:255'],
            'response' => ['required', 'string'],
        ];
    }
}
