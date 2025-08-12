<?php

namespace FluxErp\Actions\EventSubscription;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EventSubscription;
use FluxErp\Rulesets\EventSubscription\CreateEventSubscriptionRuleset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateEventSubscription extends FluxAction
{
    public static function models(): array
    {
        return [EventSubscription::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateEventSubscriptionRuleset::class;
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

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(EventSubscription::class, 'query')
            ->where('channel', $this->getData('channel'))
            ->where('event', $this->getData('event'))
            ->where('subscribable_type', $this->getData('subscribable_type'))
            ->where('subscribable_id', $this->getData('subscribable_id'))
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'subscription' => [__('Already subscribed')],
            ])->errorBag('createEventSubscription');
        }
    }
}
