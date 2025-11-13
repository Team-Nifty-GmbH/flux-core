<?php

namespace FluxErp\Rulesets\Calendar;

use FluxErp\Actions\Calendar\CreatePublicCalendar;
use FluxErp\Models\Calendar;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateCalendarRuleset extends FluxRuleset
{
    protected static ?string $model = Calendar::class;

    public static function getRules(): array
    {
        $rules = parent::getRules();

        if (! auth()->user()?->can('action.' . resolve_static(CreatePublicCalendar::class, 'name'))) {
            $rules = array_diff_key($rules, array_flip(['is_public']));
        }

        return $rules;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required_without:model_type',
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                (app(ModelExists::class, ['model' => Calendar::class]))
                    ->where('is_group', true),
            ],
            'model_type' => [
                'nullable',
                app(MorphClassExists::class),
            ],
            'name' => 'required|string|max:255',
            'description' => 'string|nullable',
            'color' => [
                'string',
                'regex:/^(\#[\da-f]{3}|\#[\da-f]{6}|rgba\(((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*,\s*){2}((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*)(,\s*(0\.\d+|1))\)|hsla\(\s*((\d{1,2}|[1-2]\d{2}|3([0-5]\d|60)))\s*,\s*((\d{1,2}|100)\s*%)\s*,\s*((\d{1,2}|100)\s*%)(,\s*(0\.\d+|1))\)|rgb\(((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*,\s*){2}((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*)|hsl\(\s*((\d{1,2}|[1-2]\d{2}|3([0-5]\d|60)))\s*,\s*((\d{1,2}|100)\s*%)\s*,\s*((\d{1,2}|100)\s*%)\))$/i',
            ],
            'custom_properties' => 'exclude_if:is_group,true|array|nullable',
            'custom_properties.*.name' => 'required|string',
            'custom_properties.*.field_type' => [
                'required',
                'string',
                Rule::in([
                    'text',
                    'textarea',
                    'checkbox',
                    'date',
                ]),
            ],
            'has_repeatable_events' => 'boolean',
            'is_group' => 'boolean',
            'is_public' => 'boolean',
        ];
    }
}
