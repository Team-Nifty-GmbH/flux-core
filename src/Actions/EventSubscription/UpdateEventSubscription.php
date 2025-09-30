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
    public static function models(): array
    {
        return [EventSubscription::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateEventSubscriptionRuleset::class;
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

        $channel = $this->getData('channel');
        $event = $this->getData('event');
        if ($channel xor $event) {
            $eventSubscription = resolve_static(EventSubscription::class, 'query')
                ->whereKey($this->getData('id'))
                ->first();

            $channel ??= $eventSubscription->channel;
            $event ??= $eventSubscription->event;
        }

        if (($channel || $event)
            && resolve_static(EventSubscription::class, 'query')
                ->whereKeyNot($this->getData('id'))
                ->where('channel', $channel)
                ->where('event', $event)
                ->where('subscribable_type', Auth::user()->getMorphClass())
                ->where('subscribable_id', Auth::id())
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'subscription' => [__('Already subscribed')],
            ])->errorBag('createEventSubscription');
        }
    }
}
