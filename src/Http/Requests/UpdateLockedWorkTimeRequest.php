<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Contact;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\User;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use FluxErp\Rules\ClassExists;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\Trackable;
use Illuminate\Database\Eloquent\Model;

class UpdateLockedWorkTimeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(WorkTime::class),
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
                new ClassExists(uses: Trackable::class, instanceOf: Model::class),
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
