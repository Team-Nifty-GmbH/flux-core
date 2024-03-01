<?php

namespace FluxErp\Rulesets\Media;

use FluxErp\Models\Media;
use FluxErp\Rules\MediaUploadType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class ReplaceMediaRuleset extends FluxRuleset
{
    protected static ?string $model = Media::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                new ModelExists(Media::class),
            ],
            'media' => 'required',
            'media_type' => ['sometimes', new MediaUploadType()],
            'parent_id' => [
                'integer',
                'nullable',
                new ModelExists(Media::class),
            ],
            'name' => 'sometimes|required|string',
            'file_name' => 'sometimes|required|string',
            'disk' => [
                'sometimes',
                'required',
                'string',
                Rule::in(array_keys(config('filesystems.disks'))),
            ],
            'collection_name' => 'sometimes|required|string',
            'custom_properties' => 'sometimes|array',
        ];
    }
}
