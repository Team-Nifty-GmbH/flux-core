<?php

namespace FluxErp\Traits\Model;

use FluxErp\Models\PushSubscription;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use NotificationChannels\WebPush\HasPushSubscriptions as BaseHasPushSubscriptions;

trait HasPushSubscriptions
{
    use BaseHasPushSubscriptions;

    /**
     * @return MorphMany<PushSubscription, $this>
     */
    public function pushSubscriptions(): MorphMany
    {
        return $this->morphMany(PushSubscription::class, 'subscribable');
    }
}
