<?php

namespace FluxErp\Http\Requests;

use FluxErp\Helpers\Helper;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Rules\ArrayIsList;
use FluxErp\Rules\AvailableValidationRule;
use FluxErp\Rules\UniqueInFieldDependence;
use Illuminate\Validation\Rule;

class UpdateAdditionalColumnRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:additional_columns,id',
            'name' => [
                'sometimes',
                'required',
                'string',
                new UniqueInFieldDependence(AdditionalColumn::class, ['model_type', 'model_id'], true),
            ],
            'field_type' => [
                'sometimes',
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
