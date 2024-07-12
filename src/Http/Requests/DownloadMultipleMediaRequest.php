<?php

namespace FluxErp\Http\Requests;

class DownloadMultipleMediaRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'ids' => 'required|string',
            'filename' => 'string|nullable',
        ];
    }
}
