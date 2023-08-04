<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use Illuminate\Database\Eloquent\Model;

class DeleteMediaCollectionRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
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
            'collection_name' => 'required|string',
        ];
    }
}
