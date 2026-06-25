<?php

namespace FluxErp\Http\Requests;

class MarkNotificationsReadRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'nullable',
                'string',
            ],
            'all' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}
