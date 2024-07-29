<?php

namespace FluxErp\Rulesets\EventSubscription;

use FluxErp\Models\EventSubscription;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
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
                    ->where('user_id', auth()->id()),
            ],
            'event' => 'required|string',
            'model_type' => [
                'required',
                'string',
                app(MorphClassExists::class),
            ],
            'model_id' => [
                'present',
                'integer',
                'nullable',
                app(MorphExists::class),
            ],
            'is_broadcast' => 'required|boolean|accepted_if:is_notifiable,false,0',
            'is_notifiable' => 'required|boolean|accepted_if:is_broadcast,false,0',
        ];
    }
}
