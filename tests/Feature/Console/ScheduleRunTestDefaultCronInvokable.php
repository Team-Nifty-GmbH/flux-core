<?php

namespace FluxErp\Tests\Feature\Console;

use Cron\CronExpression;
use FluxErp\Console\Scheduling\Repeatable;

class ScheduleRunTestDefaultCronInvokable implements Repeatable
{
    public static bool $wasInvoked = false;

    public function __invoke(): void
    {
        static::$wasInvoked = true;
    }

    public static function defaultCron(): ?CronExpression
    {
        return new CronExpression('* * * * *');
    }

    public static function description(): ?string
    {
        return 'Test default cron invokable';
    }

    public static function isRepeatable(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return 'DefaultCronInvokable';
    }

    public static function parameters(): array
    {
        return [];
    }

    public static function withoutOverlapping(): bool
    {
        return false;
    }
}
