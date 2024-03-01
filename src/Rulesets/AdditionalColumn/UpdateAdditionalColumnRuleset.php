<?php

namespace FluxErp\Rulesets\AdditionalColumn;

use FluxErp\Helpers\Helper;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Rules\ArrayIsList;
use FluxErp\Rules\AvailableValidationRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\UniqueInFieldDependence;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateAdditionalColumnRuleset extends FluxRuleset
{
    protected static ?string $model = AdditionalColumn::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(AdditionalColumn::class),
            ],
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
