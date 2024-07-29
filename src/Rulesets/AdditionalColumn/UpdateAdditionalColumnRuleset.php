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
                app(ModelExists::class, ['model' => AdditionalColumn::class]),
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                app(
                    UniqueInFieldDependence::class,
                    [
                        'model' => AdditionalColumn::class,
                        'dependingField' => ['model_type', 'model_id'],
                        'ignoreSelf' => true,
                    ]
                ),
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
                app(AvailableValidationRule::class),
            ],
            'values' => [
                'array',
                app(ArrayIsList::class),
            ],
            'is_customer_editable' => 'boolean',
            'is_translatable' => 'boolean',
            'is_frontend_visible' => 'boolean',
        ];
    }
}
