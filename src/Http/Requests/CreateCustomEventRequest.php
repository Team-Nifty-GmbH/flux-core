<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use Illuminate\Database\Eloquent\Model;

class CreateCustomEventRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|alpha|unique:custom_events,name',
            'model_type' => [
                'string',
                'nullable',
                new ClassExists(instanceOf: Model::class),
            ],
            'model_id' => [
                'required_with:model_type',
                'integer',
                new MorphExists(),
            ],
        ];
    }
}
