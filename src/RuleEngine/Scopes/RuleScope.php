<?php

namespace FluxErp\RuleEngine\Scopes;

use Carbon\Carbon;

abstract class RuleScope
{
    public Carbon $now;

    public function __construct(?Carbon $now = null)
    {
        $this->now = $now ?? Carbon::now();
    }
}
