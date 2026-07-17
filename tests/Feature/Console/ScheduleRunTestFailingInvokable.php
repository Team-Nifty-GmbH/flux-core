<?php

namespace FluxErp\Tests\Feature\Console;

use RuntimeException;

class ScheduleRunTestFailingInvokable
{
    public static int $invocationCount = 0;

    public function __invoke(): void
    {
        static::$invocationCount++;

        throw new RuntimeException('Invokable failed');
    }
}
