<?php

namespace FluxErp\Tests\Feature\Console;

class ScheduleRunTestFalseInvokable
{
    public function __invoke(): bool
    {
        return false;
    }
}
