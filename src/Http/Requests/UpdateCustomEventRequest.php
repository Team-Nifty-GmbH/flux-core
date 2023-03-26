<?php

namespace FluxErp\Http\Requests;

class UpdateCustomEventRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:custom_events,id',
            'name' => 'required|alpha|unique:custom_events,name',
        ];
    }
}
