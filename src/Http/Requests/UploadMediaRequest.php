<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\ExistsWithIgnore;
use FluxErp\Rules\MediaUploadType;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class UploadMediaRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'model_type' => qualify_model($this->model_type),
            'media_type' => $this->media_type ?? null,
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
            'model_type' => [
                'required',
                'string',
                new ClassExists(InteractsWithMedia::class, Model::class),
            ],
            'model_id' => [
                'required',
                'integer',
                new MorphExists(),
            ],
            'parent_id' => [
                'integer',
                'nullable',
                new ExistsWithIgnore('media', 'id'),
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
