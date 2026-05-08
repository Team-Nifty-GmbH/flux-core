<?php

namespace FluxErp\Http\Requests;

class PasskeyBridgeExchangeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:64'],
            'code_verifier' => ['required', 'string', 'min:43', 'max:128'],
        ];
    }
}
