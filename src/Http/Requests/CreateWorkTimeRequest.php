<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Contact;
use FluxErp\Models\User;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use FluxErp\Rules\ClassExists;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Traits\Trackable;
use Illuminate\Database\Eloquent\Model;

class CreateWorkTimeRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->user()->id,
        ]);
    }

    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:work_times,uuid',
            'contact_id' => [
                'nullable',
                'integer',
                new ModelExists(Contact::class),
            ],
            'user_id' => [
                'required',
                'integer',
                (new ModelExists(User::class))->where('is_active', true),
            ],
            'parent_id' => [
                'required_if:is_pause,true',
                'required_if:is_daily_work_time,false',
                'nullable',
                'integer',
                new ModelExists(WorkTime::class),
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
            'started_at' => 'required_with:ended_at|nullable|date_format:Y-m-d H:i:s|before:now',
            'ended_at' => 'nullable|date_format:Y-m-d H:i:s|after:started_at',
            'name' => 'required_unless:is_daily_work_time,true|string|nullable',
            'description' => 'string|nullable',
            'is_billable' => 'nullable|boolean',
            'is_daily_work_time' => 'boolean',
            'is_locked' => 'boolean',
            'is_pause' => 'boolean',
        ];
    }
}
