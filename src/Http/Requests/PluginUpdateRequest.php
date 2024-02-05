<?php

namespace FluxErp\Http\Requests;

class PluginUpdateRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'packages' => 'present|array',
            'packages.*' => 'required|string',
            'migrate' => 'boolean',
        ];
    }
}
