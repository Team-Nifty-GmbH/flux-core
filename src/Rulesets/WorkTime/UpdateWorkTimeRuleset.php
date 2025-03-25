<?php

namespace FluxErp\Rulesets\WorkTime;

use FluxErp\Models\Contact;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
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
                app(ModelExists::class, ['model' => WorkTime::class])
                    ->where('is_locked', false),
            ],
            'contact_id' => [
                'nullable',
                'required_if:is_billable,true',
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
            'ended_at' => 'nullable|date',
            'name' => 'exclude_if:is_daily_work_time,true|string|nullable',
            'description' => 'string|nullable',
            'is_billable' => 'nullable|boolean',
            'is_locked' => 'boolean',
        ];
    }
}
