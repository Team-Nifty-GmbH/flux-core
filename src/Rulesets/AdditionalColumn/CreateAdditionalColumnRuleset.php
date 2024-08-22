<?php

namespace FluxErp\Rulesets\AdditionalColumn;

use FluxErp\Helpers\Helper;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Rules\ArrayIsList;
use FluxErp\Rules\AvailableValidationRule;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
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
                app(
                    UniqueInFieldDependence::class,
                    [
                        'model' => AdditionalColumn::class,
                        'dependingField' => ['model_type', 'model_id'],
                        'ignoreSelf' => false,
                    ]
                ),
            ],
            'model_type' => [
                'required',
                'string',
                app(MorphClassExists::class),
            ],
            'model_id' => [
                'integer',
                'nullable',
                app(MorphExists::class),
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
