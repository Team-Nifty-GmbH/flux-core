<?php

namespace FluxErp\Http\Requests;

class DispatchCustomEventRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
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
