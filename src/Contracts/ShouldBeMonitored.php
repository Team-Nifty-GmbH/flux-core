<?php

namespace FluxErp\Contracts;

interface ShouldBeMonitored
{
    public static function keepMonitorOnSuccess(): bool;
}
