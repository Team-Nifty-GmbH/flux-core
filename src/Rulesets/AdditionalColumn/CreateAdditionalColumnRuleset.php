<?php

namespace FluxErp\Rulesets\AdditionalColumn;

use FluxErp\Helpers\Helper;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Rules\ArrayIsList;
use FluxErp\Rules\AvailableValidationRule;
use FluxErp\Rules\MorphExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\UniqueInFieldDependence;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateAdditionalColumnRuleset extends FluxRuleset
{
    protected static ?string $model = AdditionalColumn::class;

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
                new MorphClassExists(),
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
