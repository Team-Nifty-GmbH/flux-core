<?php

namespace FluxErp\Http\Requests;

class PluginUpdateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'packages' => 'required|array',
            'packages.*' => 'required|string',
            'migrate' => 'boolean',
        ];
    }
}
