<?php

namespace FluxErp\Traits;

use FluxErp\Models\EventSubscription;

trait HasNotificationSubscriptions
{
    public function eventSubscriptions()
    {
        return $this->morphMany(EventSubscription::class, 'subscribable');
    }
}
