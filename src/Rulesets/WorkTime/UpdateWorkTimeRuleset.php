<?php

namespace FluxErp\Rulesets\WorkTime;

use FluxErp\Models\Contact;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\Trackable;

class UpdateWorkTimeRuleset extends FluxRuleset
{
    protected static ?string $model = WorkTime::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                (new ModelExists(WorkTime::class))->where('is_locked', false),
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
            'ended_at' => 'nullable|date_format:Y-m-d H:i:s',
            'name' => 'exclude_if:is_daily_work_time,true|string|nullable',
            'description' => 'string|nullable',
            'is_billable' => 'nullable|boolean',
            'is_locked' => 'boolean',
        ];
    }
}
