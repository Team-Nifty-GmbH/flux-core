<?php

namespace FluxErp\Rulesets\WorkTime;

use FluxErp\Models\Contact;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\User;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\Trackable;

class UpdateLockedWorkTimeRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = WorkTime::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => WorkTime::class]),
            ],
            'user_id' => [
                'integer',
                (app(ModelExists::class, ['model' => User::class]))->where('is_active', true),
            ],
            'contact_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'order_position_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => OrderPosition::class]),
            ],
            'work_time_type_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => WorkTimeType::class]),
            ],
            'trackable_type' => [
                'required_with:trackable_id',
                'string',
                app(MorphClassExists::class, ['uses' => Trackable::class]),
            ],
            'trackable_id' => [
                'required_with:trackable_type',
                'integer',
                app(MorphExists::class, ['modelAttribute' => 'trackable_type']),
            ],
            'started_at' => 'required_with:ended_at|date|before:ended_at',
            'ended_at' => 'nullable|date|after:started_at',
            'paused_time_ms' => 'integer|nullable|min:0',
            'name' => 'exclude_if:is_daily_work_time,true|string|nullable',
            'description' => 'string|nullable',
            'is_billable' => 'nullable|boolean',
            'is_locked' => 'boolean',
        ];
    }
}
