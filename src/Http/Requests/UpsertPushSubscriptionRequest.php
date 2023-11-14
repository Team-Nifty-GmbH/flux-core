<?php

namespace FluxErp\Http\Requests;

class UpsertPushSubscriptionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'endpoint' => [
                'required',
                'url',
            ],
            'keys' => [
                'required',
                'array',
            ],
            'keys.auth' => [
                'required',
                'string',
            ],
            'keys.p256dh' => [
                'required',
                'string',
            ],
        ];
    }
}
