<?php

namespace FluxErp\Actions\EventSubscription;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EventSubscription;
use FluxErp\Rulesets\EventSubscription\CreateEventSubscriptionRuleset;
use Illuminate\Support\Facades\Auth;

class CreateEventSubscription extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateEventSubscriptionRuleset::class;
    }

    public static function models(): array
    {
        return [EventSubscription::class];
    }

    public function performAction(): EventSubscription
    {
        $eventSubscription = app(EventSubscription::class, ['attributes' => $this->data]);
        $eventSubscription->save();

        return $eventSubscription->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['subscribable_id'] ??= Auth::id();
        $this->data['subscribable_type'] ??= Auth::user()->getMorphClass();
    }
}
