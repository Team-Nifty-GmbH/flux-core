<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Media;
use FluxErp\Rules\MediaUploadType;
use FluxErp\Rules\ModelExists;
use Illuminate\Validation\Rule;

class ReplaceMediaRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->id,
            'media_type' => $this->media_type ?? null,
            'model_type' => Media::query()->whereKey($this->id)->first()?->model_type,
        ]);
    }

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
