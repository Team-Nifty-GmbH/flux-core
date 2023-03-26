<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Media;
use FluxErp\Rules\MediaUploadType;
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|exists:media',
            'media' => 'required',
            'media_type' => ['sometimes', new MediaUploadType()],
            'parent_id' => 'integer|nullable|exists:media,id,deleted_at,NULL',
            'name' => 'sometimes|required|string',
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
