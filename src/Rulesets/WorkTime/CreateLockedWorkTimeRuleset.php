<?php

namespace FluxErp\Rulesets\WorkTime;

use FluxErp\Models\Contact;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\Model\Trackable;

class CreateLockedWorkTimeRuleset extends CreateWorkTimeRuleset
{
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'contact_id' => [
                    'exclude_if:is_daily_work_time,true',
                    'required_if:is_billable,true',
                    'nullable',
                    'integer',
                    app(ModelExists::class, ['model' => Contact::class]),
                ],
                'parent_id' => [
                    'exclude_if:is_daily_work_time,true',
                    'nullable',
                    'integer',
                    app(ModelExists::class, ['model' => WorkTime::class]),
                ],
                'work_time_type_id' => [
                    'exclude_if:is_daily_work_time,true',
                    'nullable',
                    'integer',
                    app(ModelExists::class, ['model' => WorkTimeType::class]),
                ],
                'trackable_type' => [
                    'exclude_if:is_daily_work_time,true',
                    'required_with:trackable_id',
                    'string',
                    'max:255',
                    app(MorphClassExists::class, ['uses' => Trackable::class]),
                ],
                'trackable_id' => [
                    'exclude_if:is_daily_work_time,true',
                    'required_with:trackable_type',
                    'integer',
                    app(MorphExists::class, ['modelAttribute' => 'trackable_type']),
                ],
                'started_at' => 'required|date',
                'ended_at' => [
                    'required',
                    'date',
                    'after:started_at',
                ],
                'paused_time_ms' => 'integer|nullable|min:0',
                'is_billable' => [
                    'exclude_if:is_daily_work_time,true',
                    'nullable',
                    'boolean',
                ],
            ]
        );
    }
}
