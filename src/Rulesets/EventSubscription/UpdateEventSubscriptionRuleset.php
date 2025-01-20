<?php

namespace FluxErp\Rulesets\EventSubscription;

use FluxErp\Models\EventSubscription;
use FluxErp\Rules\ModelExists;
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
                app(ModelExists::class, ['model' => EventSubscription::class])
                    ->where('subscribable_id', auth()->id())
                    ->where('subscribable_type', auth()->user()->getMorphClass()),
            ],
            'channel' => 'string',
            'is_broadcast' => 'boolean|accepted_if:is_notifiable,false,0',
            'is_notifiable' => 'boolean|accepted_if:is_broadcast,false,0',
        ];
    }
}
