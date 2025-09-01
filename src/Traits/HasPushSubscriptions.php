<?php

namespace FluxErp\Traits;

use FluxErp\Models\PushSubscription;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use NotificationChannels\WebPush\HasPushSubscriptions as BaseHasPushSubscriptions;

trait HasPushSubscriptions
{
    use BaseHasPushSubscriptions;

    public function pushSubscriptions(): MorphMany
    {
        return $this->morphMany(PushSubscription::class, 'subscribable');
    }
}
