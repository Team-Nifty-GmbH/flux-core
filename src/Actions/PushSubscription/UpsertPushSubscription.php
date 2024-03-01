<?php

namespace FluxErp\Actions\PushSubscription;

use FluxErp\Actions\FluxAction;
use FluxErp\Rulesets\PushSubscription\UpsertPushSubscriptionRuleset;
use NotificationChannels\WebPush\PushSubscription;

class UpsertPushSubscription extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpsertPushSubscriptionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [PushSubscription::class];
    }

    public function performAction(): PushSubscription
    {
        return auth()->user()->updatePushSubscription(
            $this->data['endpoint'],
            $this->data['keys']['p256dh'],
            $this->data['keys']['auth']
        );
    }
}
