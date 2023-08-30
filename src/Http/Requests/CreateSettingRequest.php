<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use Illuminate\Database\Eloquent\Model;

class CreateSettingRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:settings,uuid',
            'key' => 'required|string|unique:settings,key',
            'model_type' => [
                'required',
                'string',
                new ClassExists(instanceOf: Model::class),
            ],
            'model_id' => [
                'required',
                'integer',
                new MorphExists(),
            ],
            'settings' => 'required|array',
        ];
    }
}
