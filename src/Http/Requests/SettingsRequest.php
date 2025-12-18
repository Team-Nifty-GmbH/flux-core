<?php

namespace FluxErp\Http\Requests;

class SettingsRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'group' => 'string|exclude_with:groups',
            'groups' => 'array',
            'groups.*' => 'required|string',
        ];
    }
}
