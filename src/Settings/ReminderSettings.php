<?php

namespace FluxErp\Settings;

class ReminderSettings extends FluxSettings
{
    public bool $has_start_reminder = false;

    public int $start_reminder_minutes_before = 15;

    public bool $has_end_reminder = false;

    public int $end_reminder_minutes_before = 15;

    public static function group(): string
    {
        return 'reminder';
    }
}
