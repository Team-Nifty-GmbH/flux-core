<?php

namespace FluxErp\Rulesets\EventSubscription;

use FluxErp\Models\EventSubscription;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateEventSubscriptionRuleset extends FluxRuleset
{
    protected static ?string $model = EventSubscription::class;

    public function rules(): array
    {
        return [
            'channel' => 'required|string',
            'subscribable_type' => [
                'required',
                'string',
                app(MorphClassExists::class),
            ],
            'subscribable_id' => [
                'required',
                'integer',
                app(MorphExists::class, ['modelAttribute' => 'subscribable_type']),
            ],
            'is_broadcast' => 'required|boolean|accepted_if:is_notifiable,false,0',
            'is_notifiable' => 'required|boolean|accepted_if:is_broadcast,false,0',
        ];
    }
}
