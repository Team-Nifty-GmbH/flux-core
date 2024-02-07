<?php

namespace FluxErp\Http\Requests;

class PluginToggleActiveRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'packages' => 'required|array',
            'packages.*' => 'required|string',
        ];
    }
}
