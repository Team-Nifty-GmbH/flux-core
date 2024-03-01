<?php

namespace FluxErp\Rulesets\Media;

use FluxErp\Models\Media;
use FluxErp\Rules\MediaUploadType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\HasMedia;

class UploadMediaRuleset extends FluxRuleset
{
    protected static ?string $model = Media::class;

    public function rules(): array
    {
        return [
            'model_type' => [
                'required',
                'string',
                new MorphClassExists(implements: HasMedia::class),
            ],
            'model_id' => [
                'required',
                'integer',
                new MorphExists(),
            ],
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
            'media' => 'required',
            'media_type' => ['sometimes', new MediaUploadType()],
            'collection_name' => 'sometimes|required|string',
            'categories.*' => 'sometimes|array',
            'custom_properties' => 'sometimes|array',
        ];
    }
}
