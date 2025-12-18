<?php

namespace FluxErp\Http\Requests;

use FluxErp\Enums\DevicePlatformEnum;
use Illuminate\Validation\Rule;

class LoginMobileRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'fcm_token' => [
                'nullable',
                'string',
            ],
            'platform' => [
                'nullable',
                Rule::enum(DevicePlatformEnum::class),
            ],
            'device_id' => [
                'nullable',
                'string',
                'max:255',
            ],
            'device_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'device_model' => [
                'nullable',
                'string',
                'max:255',
            ],
            'device_manufacturer' => [
                'nullable',
                'string',
                'max:255',
            ],
            'device_os_version' => [
                'nullable',
                'string',
                'max:255',
            ],
            'redirect' => [
                'nullable',
                'string',
            ],
        ];
    }
}
