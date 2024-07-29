<?php

namespace FluxErp\Rulesets\EventSubscription;

use FluxErp\Models\EventSubscription;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteEventSubscriptionRuleset extends FluxRuleset
{
    protected static ?string $model = EventSubscription::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => EventSubscription::class])
                    ->where('subscribable_type', auth()->user()->getMorphClass())
                    ->where('subscribable_id', auth()->id()),
            ],
        ];
    }
}
