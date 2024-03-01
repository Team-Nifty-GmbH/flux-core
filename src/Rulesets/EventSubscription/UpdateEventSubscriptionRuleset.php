<?php

namespace FluxErp\Rulesets\EventSubscription;

use FluxErp\Models\EventSubscription;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateEventSubscriptionRuleset extends FluxRuleset
{
    protected static ?string $model = EventSubscription::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                (new ModelExists(EventSubscription::class))->where('user_id', auth()->id()),
            ],
            'event' => 'required|string',
            'model_type' => [
                'required',
                'string',
                new MorphClassExists(),
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
