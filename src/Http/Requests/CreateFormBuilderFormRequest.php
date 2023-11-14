<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use Illuminate\Database\Eloquent\Model;

class CreateFormBuilderFormRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'model_type' => [
                'required_with:model_id',
                'string',
                new ClassExists(instanceOf: Model::class),
            ],
            'model_id' => [
                'required_with:model_type',
                'integer',
                new MorphExists(),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'start_date' => 'present|nullable|datetime:Y-m-d H:i:s',
            'end_date' => 'present|nullable|after:start_date|datetime:Y-m-d H:i:s',
            'is_active' => 'boolean',
        ];
    }
}
