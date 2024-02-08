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

class CreateAdditionalColumnRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                new UniqueInFieldDependence(AdditionalColumn::class, ['model_type', 'model_id'], false),
            ],
            'model_type' => [
                'required',
                'string',
                new ClassExists(instanceOf: Model::class),
            ],
            'model_id' => [
                'integer',
                'nullable',
                new MorphExists(),
            ],
            'field_type' => [
                'required',
                'string',
                Rule::in(Helper::getHtmlInputFieldTypes()),
            ],
            'label' => 'string|nullable',
            'validations' => 'array',
            'validations.*' => [
                'required',
                'string',
                new AvailableValidationRule(),
            ],
            'values' => [
                'array',
                new ArrayIsList(),
            ],
            'is_customer_editable' => 'boolean',
            'is_translatable' => 'boolean',
            'is_frontend_visible' => 'boolean',
        ];
    }
}
