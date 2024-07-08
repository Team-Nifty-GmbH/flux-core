<?php

namespace FluxErp\Http\Requests;

class DownloadMultipleMediaRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'ids' => 'required|array|string',
            'filename' => 'string|nullable',
        ];
    }
}
