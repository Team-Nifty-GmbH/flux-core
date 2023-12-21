<?php

namespace FluxErp\Console\Scheduling;

interface Repeatable
{
    public static function isRepeatable(): bool;

    public static function name(): string;

    public static function description(): ?string;

    public static function parameters(): array;
}
