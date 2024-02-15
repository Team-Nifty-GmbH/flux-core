<?php

namespace FluxErp\Http\Requests;

class DispatchCustomEventRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'event' => [
                'required',
                'string',
                'exists:custom_events,name',
            ],
            'payload' => [
                'sometimes',
                'array',
                'nullable',
            ],
        ];
    }
}
