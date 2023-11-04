<?php

namespace FluxErp\Actions\PushSubscription;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpsertPushSubscriptionRequest;
use NotificationChannels\WebPush\PushSubscription;

class UpsertPushSubscription extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpsertPushSubscriptionRequest())->rules();

    }

    public static function models(): array
    {
        return [PushSubscription::class];
    }

    public function performAction(): PushSubscription
    {
        $user = auth()->user();

        return $user->updatePushSubscription(
            $this->data['endpoint'],
            $this->data['keys']['p256dh'],
            $this->data['keys']['auth']
        );
    }
}
