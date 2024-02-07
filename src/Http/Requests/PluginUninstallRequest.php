<?php

namespace FluxErp\Http\Requests;

class PluginUninstallRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'packages' => 'required|array',
            'packages.*' => 'required|string',
            'rollback' => 'boolean',
        ];
    }
}
