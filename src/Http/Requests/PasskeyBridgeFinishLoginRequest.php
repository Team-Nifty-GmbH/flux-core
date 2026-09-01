<?php

namespace FluxErp\Http\Requests;

class PasskeyBridgeFinishLoginRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:64'],
            'response' => ['required', 'string'],
        ];
    }
}
