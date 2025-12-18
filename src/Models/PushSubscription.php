<?php

namespace FluxErp\Models;

use FluxErp\Traits\Model\BroadcastsEvents;
use FluxErp\Traits\Model\ResolvesRelationsThroughContainer;

class PushSubscription extends \NotificationChannels\WebPush\PushSubscription
{
    use BroadcastsEvents, ResolvesRelationsThroughContainer;
}
