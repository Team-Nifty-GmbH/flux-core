<?php

namespace FluxErp\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Events\BroadcastNotificationCreated;

class BroadcastNowNotificationCreated extends BroadcastNotificationCreated implements ShouldBroadcastNow
{
}
