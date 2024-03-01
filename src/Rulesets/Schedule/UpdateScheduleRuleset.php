<?php

namespace FluxErp\Rulesets\Schedule;

use FluxErp\Enums\FrequenciesEnum;
use FluxErp\Models\Schedule;
use FluxErp\Rules\Frequency;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateScheduleRuleset extends FluxRuleset
{
    protected static ?string $model = Schedule::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Schedule::class),
            ],
            'description' => 'string|nullable',
            'cron' => 'required|array',
            'cron.methods' => 'required|array',
            'cron.methods.basic' => [
                'nullable',
                Rule::in(FrequenciesEnum::getBasicFrequencies()),
            ],
            'cron.methods.dayConstraint' => [
                'nullable',
                Rule::in(FrequenciesEnum::getDayConstraints()),
            ],
            'cron.methods.timeConstraint' => [
                'nullable',
                Rule::in(FrequenciesEnum::getTimeConstraints()),
            ],
            'cron.parameters' => 'required|array',
            'cron.parameters.basic' => [
                'array',
                new Frequency('cron.methods.basic'),
            ],
            'cron.parameters.dayConstraint' => [
                'array',
                new Frequency('cron.methods.dayConstraint'),
            ],
            'cron.parameters.timeConstraint' => [
                'array',
                new Frequency('cron.methods.timeConstraint'),
            ],
            'parameters' => 'array',
            'due_at' => 'date|nullable',
            'ends_at' => 'date|nullable',
            'recurrences' => 'exclude_unless:ends_at,null|nullable|integer|min:1',
            'is_active' => 'boolean',
        ];
    }
}
