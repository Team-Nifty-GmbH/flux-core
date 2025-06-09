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
                app(ModelExists::class, ['model' => Media::class]),
            ],
            'media' => 'required',
            'media_type' => ['sometimes', app(MediaUploadType::class)],
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Media::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'file_name' => 'sometimes|required|string|max:255',
            'disk' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::in(array_keys(config('filesystems.disks'))),
            ],
            'collection_name' => 'sometimes|required|string|max:255',
            'custom_properties' => 'sometimes|array',
        ];
    }
}
