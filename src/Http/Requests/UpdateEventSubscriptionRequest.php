<?php

namespace FluxErp\Http\Requests;

class UpdateEventSubscriptionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer',
            'event' => 'required|string',
            'model_type' => 'required|string',
            'model_id' => 'present|integer|nullable',
            'is_broadcast' => 'required|boolean|accepted_if:is_notifiable,false,0',
            'is_notifiable' => 'required|boolean|accepted_if:is_broadcast,false,0',
        ];
    }
}
