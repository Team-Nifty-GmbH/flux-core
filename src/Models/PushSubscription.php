<?php

namespace FluxErp\Models;

use FluxErp\Traits\BroadcastsEvents;
use FluxErp\Traits\ResolvesRelationsThroughContainer;

class PushSubscription extends \NotificationChannels\WebPush\PushSubscription
{
    use BroadcastsEvents, ResolvesRelationsThroughContainer;
}
