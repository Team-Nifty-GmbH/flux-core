<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\User;
use FluxErp\Rules\ClassExists;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphExists;
use Illuminate\Database\Eloquent\Model;

class CreateEventSubscriptionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'event' => 'required|string',
            'user_id' => [
                'sometimes',
                'integer',
                new ModelExists(User::class),
            ],
            'model_type' => [
                'required',
                'string',
                new ClassExists(instanceOf: Model::class),
            ],
            'model_id' => [
                'present',
                'integer',
                'nullable',
                new MorphExists(),
            ],
            'is_broadcast' => 'required|boolean|accepted_if:is_notifiable,false,0',
            'is_notifiable' => 'required|boolean|accepted_if:is_broadcast,false,0',
        ];
    }
}
