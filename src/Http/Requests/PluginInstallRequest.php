<?php

namespace FluxErp\Http\Requests;

class PluginInstallRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'packages' => 'required|array',
            'packages.*' => 'required|string',
            'options' => 'array',
            'options.*' => 'string',
            'migrate' => 'boolean',
        ];
    }
}
