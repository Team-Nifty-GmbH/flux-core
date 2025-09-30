<?php

namespace FluxErp\Rulesets\WorkTime;

use FluxErp\Models\Contact;
use FluxErp\Models\Employee;
use FluxErp\Models\User;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\Trackable;

class CreateWorkTimeRuleset extends FluxRuleset
{
    protected static ?string $model = WorkTime::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:work_times,uuid',
            'contact_id' => [
                'nullable',
                'required_if:is_billable,true',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'user_id' => [
                'required_without:employee_id',
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => User::class])
                    ->where('is_active', true),
            ],
            'employee_id' => [
                'required_without:user_id',
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Employee::class])
                    ->where('is_active', true),
            ],
            'parent_id' => [
                'required_if:is_pause,true',
                'required_if:is_daily_work_time,false',
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => WorkTime::class]),
            ],
            'work_time_type_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => WorkTimeType::class]),
            ],
            'trackable_type' => [
                'required_with:trackable_id',
                'string',
                'max:255',
                app(MorphClassExists::class, ['uses' => Trackable::class]),
            ],
            'trackable_id' => [
                'required_with:trackable_type',
                'integer',
                app(MorphExists::class, ['modelAttribute' => 'trackable_type']),
            ],
            'started_at' => 'required_with:ended_at|nullable|date_format:Y-m-d H:i:s|before_or_equal:now',
            'ended_at' => 'nullable|date_format:Y-m-d H:i:s|after:started_at',
            'name' => 'required_unless:is_daily_work_time,true|string|max:255|nullable',
            'description' => 'string|nullable',
            'is_billable' => 'nullable|boolean',
            'is_daily_work_time' => 'boolean',
            'is_locked' => 'boolean',
            'is_pause' => 'boolean',
        ];
    }
}
