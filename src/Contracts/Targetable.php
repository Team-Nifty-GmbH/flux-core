<?php

namespace FluxErp\Contracts;

interface Targetable
{
    public static function aggregateColumns(string $type): array;

    public static function aggregateTypes(): array;

    public static function ownerColumns(): array;

    public static function timeframeColumns(): array;
}
