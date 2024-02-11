<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use Illuminate\Database\Eloquent\Model;

class DeleteMediaCollectionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
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
            'collection_name' => 'required|string',
        ];
    }
}
