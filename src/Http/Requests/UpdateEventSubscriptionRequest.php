<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\EventSubscription;
use FluxErp\Rules\ClassExists;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphExists;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UpdateEventSubscriptionRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                (new ModelExists(EventSubscription::class))->where('user_id', Auth::id()),
            ],
            'event' => 'required|string',
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
