<?php

namespace FluxErp\Http\Requests;

class DeleteDeviceTokenRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'device_id' => 'required|string|max:255',
        ];
    }
}
