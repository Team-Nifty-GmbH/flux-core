<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ClassExists;
use FluxErp\Rules\ExistsWithIgnore;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\Trackable;
use Illuminate\Database\Eloquent\Model;

class UpdateLockedWorkTimeRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:work_times,id,deleted_at,NULL',
            'user_id' => 'integer|exists:users,id,is_active,1,deleted_at,NULL',
            'contact_id' => [
                'nullable',
                'integer',
                (new ExistsWithIgnore('contacts', 'id'))->whereNull('deleted_at'),
            ],
            'order_position_id' => [
                'nullable',
                'integer',
                (new ExistsWithIgnore('order_positions', 'id'))->whereNull('deleted_at'),
            ],
            'work_time_type_id' => [
                'nullable',
                'integer',
                (new ExistsWithIgnore('work_time_types', 'id'))->whereNull('deleted_at'),
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
