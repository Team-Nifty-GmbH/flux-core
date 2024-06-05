<?php

namespace FluxErp\Support\Bus;

use Illuminate\Bus\Dispatcher as BaseDispatcher;
use Illuminate\Support\Collection;

class Dispatcher extends BaseDispatcher
{
    public function monitoredBatch(Collection|array $jobs): MonitorablePendingBatch
    {
        return new MonitorablePendingBatch($this->container, Collection::wrap($jobs));
    }
}
