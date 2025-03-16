<?php

namespace FluxErp\Actions\PushSubscription;

use FluxErp\Actions\FluxAction;
use FluxErp\Rulesets\PushSubscription\UpsertPushSubscriptionRuleset;
use NotificationChannels\WebPush\PushSubscription;

class UpsertPushSubscription extends FluxAction
{
    public static function models(): array
    {
        return [PushSubscription::class];
    }

    protected function getRulesets(): string|array
    {
        return UpsertPushSubscriptionRuleset::class;
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
