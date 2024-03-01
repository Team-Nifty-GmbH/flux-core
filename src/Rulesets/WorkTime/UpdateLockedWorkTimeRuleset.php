<?php

namespace FluxErp\Rulesets\WorkTime;

use FluxErp\Livewire\WorkTime;
use FluxErp\Models\Contact;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\User;
use FluxErp\Models\WorkTimeType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\Trackable;

class UpdateLockedWorkTimeRuleset extends FluxRuleset
{
    protected static ?string $model = WorkTime::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(\FluxErp\Models\WorkTime::class),
            ],
            'user_id' => [
                'integer',
                (new ModelExists(User::class))->where('is_active', true),
            ],
            'contact_id' => [
                'nullable',
                'integer',
                new ModelExists(Contact::class),
            ],
            'order_position_id' => [
                'nullable',
                'integer',
                new ModelExists(OrderPosition::class),
            ],
            'work_time_type_id' => [
                'nullable',
                'integer',
                new ModelExists(WorkTimeType::class),
            ],
            'trackable_type' => [
                'required_with:trackable_id',
                'string',
                new MorphClassExists(uses: Trackable::class),
            ],
            'trackable_id' => [
                'required_with:trackable_type',
                'integer',
                new MorphExists('trackable_type'),
            ],
            'started_at' => 'required_with:ended_at|date_format:Y-m-d H:i:s|before:ended_at',
            'ended_at' => 'nullable|date_format:Y-m-d H:i:s|after:started_at',
            'paused_time_ms' => 'integer|nullable|min:0',
            'name' => 'exclude_if:is_daily_work_time,true|string|nullable',
            'description' => 'string|nullable',
            'is_billable' => 'nullable|boolean',
            'is_locked' => 'boolean',
        ];
    }
}
