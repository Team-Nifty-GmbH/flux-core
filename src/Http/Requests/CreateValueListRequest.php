<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Traits\HasAdditionalColumns;
use Illuminate\Database\Eloquent\Model;

class CreateValueListRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'model_type' => [
                'required',
                'string',
                new ClassExists(uses: HasAdditionalColumns::class, instanceOf: Model::class),
            ],
            'values' => 'required|array',
        ];
    }
}
