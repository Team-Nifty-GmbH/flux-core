<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use Illuminate\Database\Eloquent\Model;

class UpdateFormBuilderFormRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|exists:form_builder_forms,id,deleted_at,NULL',
            'model_type' => [
                'nullable',
                'required_with:model_id',
                'string',
                new ClassExists(instanceOf: Model::class),
            ],
            'model_id' => [
                'nullable',
                'required_with:model_type',
                'integer',
                new MorphExists(),
            ],
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'nullable|string|max:255',
            'options' => 'nullable|array',
            'start_date' => 'present|nullable|date:Y-m-d H:i:s',
            'end_date' => 'present|nullable|after:start_date|date:Y-m-d H:i:s',
            'is_active' => 'boolean',
        ];
    }
}
