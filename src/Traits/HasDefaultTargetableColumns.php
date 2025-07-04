<?php

namespace FluxErp\Traits;

trait HasDefaultTargetableColumns
{
    public static function aggregateColumns(string $type): array
    {
        return match ($type) {
            'count' => ['id'],
            default => [],
        };
    }

    public static function aggregateTypes(): array
    {
        return [
            'count',
        ];
    }

    public static function ownerColumns(): array
    {
        return [
            'created_by',
            'updated_by',
        ];
    }

    public static function timeframeColumns(): array
    {
        return [
            'created_at',
            'updated_at',
        ];
    }
}
