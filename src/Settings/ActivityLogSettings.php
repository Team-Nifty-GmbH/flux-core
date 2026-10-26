<?php

namespace FluxErp\Settings;

class ActivityLogSettings extends FluxSettings
{
    public ?int $created_retention_days;

    public ?int $updated_retention_days;

    public ?int $deleted_retention_days;

    public ?int $other_retention_days;

    public static function group(): string
    {
        return 'activity_log';
    }

    public static function rules(): array
    {
        return [
            'created_retention_days' => ['nullable', 'integer', 'min:1'],
            'updated_retention_days' => ['nullable', 'integer', 'min:1'],
            'deleted_retention_days' => ['nullable', 'integer', 'min:1'],
            'other_retention_days' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
