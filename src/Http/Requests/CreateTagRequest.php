<?php

namespace FluxErp\Http\Requests;

use FluxErp\Helpers\Helper;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Rules\ArrayIsList;
use FluxErp\Rules\AvailableValidationRule;
use FluxErp\Rules\ClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rules\UniqueInFieldDependence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class CreateTagRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'string',
                'max:255',
            ],
            'type' => 'string|max:255',
            'color' => 'nullable|hex_color',
            'order_column' => 'nullable|integer',
        ];
    }
}
