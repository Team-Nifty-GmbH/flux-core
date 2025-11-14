<?php

namespace FluxErp\Livewire\Forms\Settings;

use FluxErp\Settings\ReminderSettings;

class ReminderSettingsForm extends SettingsForm
{
    public bool $has_start_reminder = false;

    public int $start_reminder_minutes_before = 15;

    public bool $has_end_reminder = false;

    public int $end_reminder_minutes_before = 15;

    public function getSettingsClass(): string
    {
        return ReminderSettings::class;
    }
}
