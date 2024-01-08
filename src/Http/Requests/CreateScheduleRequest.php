<?php

namespace FluxErp\Http\Requests;

use FluxErp\Enums\FrequenciesEnum;
use FluxErp\Facades\Repeatable;
use FluxErp\Rules\Frequency;
use Illuminate\Validation\Rule;

class CreateScheduleRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:schedules,uuid',
            'name' => [
                'required',
                'string',
                Rule::in(Repeatable::all()->keys()),
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
            'is_active' => 'boolean',
        ];
    }
}