<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('reminder.has_start_reminder', false);
        $this->migrator->add('reminder.start_reminder_minutes_before', 15);
        $this->migrator->add('reminder.has_end_reminder', false);
        $this->migrator->add('reminder.end_reminder_minutes_before', 15);
    }

    public function down(): void
    {
        $this->migrator->delete('reminder.has_start_reminder');
        $this->migrator->delete('reminder.start_reminder_minutes_before');
        $this->migrator->delete('reminder.end_reminder_minutes_before');
        $this->migrator->delete('reminder.has_end_reminder');
    }
};
