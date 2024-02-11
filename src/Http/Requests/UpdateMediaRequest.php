<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Media;
use FluxErp\Rules\ModelExists;

class UpdateMediaRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Media::class),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                new ModelExists(Media::class),
            ],
            'name' => 'sometimes|required|string',
            'collection' => 'sometimes|required|string',
            'categories' => 'sometimes|array',
            'custom_properties' => 'sometimes|array',
        ];
    }
}
