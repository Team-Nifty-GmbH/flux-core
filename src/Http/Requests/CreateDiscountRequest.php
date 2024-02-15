<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use Illuminate\Database\Eloquent\Model;

class CreateDiscountRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:discounts,uuid',
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
            'discount' => 'required|numeric',
            'from' => 'nullable|date_format:Y-m-d H:i:s',
            'till' => 'nullable|date_format:Y-m-d H:i:s',
            'sort_number' => 'integer|min:0',
            'is_percentage' => 'required|boolean',
        ];
    }
}
