<?php

namespace FluxErp\Http\Requests;

class PluginUploadRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'packages' => 'required|array',
            'packages.*' => 'required|file',
        ];
    }
}
