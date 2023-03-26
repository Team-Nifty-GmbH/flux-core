<?php

namespace FluxErp\Http\Requests;

class UpdateSettingRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:settings,id',
            'settings' => 'required|array',
        ];
    }
}
