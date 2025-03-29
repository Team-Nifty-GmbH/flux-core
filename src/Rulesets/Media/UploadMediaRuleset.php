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
                'max:255',
                app(MorphClassExists::class, ['implements' => HasMedia::class]),
            ],
            'model_id' => [
                'required',
                'integer',
                app(MorphExists::class),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Media::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'file_name' => 'sometimes|required|string|max:255',
            'mime_type' => 'nullable|string|max:255',
            'disk' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::in(array_keys(config('filesystems.disks'))),
            ],
            'media' => 'required',
            'media_type' => ['sometimes', app(MediaUploadType::class)],
            'collection_name' => 'sometimes|required|string|max:255',
            'categories.*' => 'sometimes|array',
            'custom_properties' => 'sometimes|array',
        ];
    }
}
