<?php

namespace FluxErp\Tests\Feature\Console;

class ScheduleRunTestInvokable
{
    public static int $invocationCount = 0;

    public function __invoke(): void
    {
        static::$invocationCount++;
    }
}
