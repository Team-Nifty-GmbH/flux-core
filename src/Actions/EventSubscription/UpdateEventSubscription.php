<?php

namespace FluxErp\Actions\EventSubscription;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\EventSubscription;
use FluxErp\Rulesets\EventSubscription\UpdateEventSubscriptionRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UpdateEventSubscription extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateEventSubscriptionRuleset::class;
    }

    public static function models(): array
    {
        return [EventSubscription::class];
    }

    public function performAction(): Model
    {
        $eventSubscription = resolve_static(EventSubscription::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $eventSubscription->fill($this->data);
        $eventSubscription->save();

        return $eventSubscription->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $this->data['subscribable_id'] ??= Auth::id();
        $this->data['subscribable_type'] ??= Auth::user()->getMorphClass();

        if (resolve_static(EventSubscription::class, 'query')
            ->whereKeyNot($this->getData('id'))
            ->where('channel', $this->getData('channel'))
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
