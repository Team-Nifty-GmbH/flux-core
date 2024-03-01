<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\MorphClassExists;
use Spatie\MediaLibrary\HasMedia;

class DownloadPublicMediaRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'model_id' => 'required|integer',
            'model_type' => [
                'required',
                'string',
                new MorphClassExists(implements: HasMedia::class),
            ],
            'conversion' => 'sometimes|required|string',
        ];
    }
}
