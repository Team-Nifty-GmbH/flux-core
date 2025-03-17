<?php

namespace FluxErp\Console\Scheduling;

use Cron\CronExpression;

interface Repeatable
{
    public static function defaultCron(): ?CronExpression;

    public static function description(): ?string;

    public static function isRepeatable(): bool;

    public static function name(): string;

    public static function parameters(): array;
}
