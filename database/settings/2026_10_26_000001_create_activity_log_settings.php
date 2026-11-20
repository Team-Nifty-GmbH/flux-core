<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('activity_log.created_retention_days');
        $this->migrator->add('activity_log.updated_retention_days', 365);
        $this->migrator->add('activity_log.deleted_retention_days');
        $this->migrator->add('activity_log.other_retention_days', 365);
    }

    public function down(): void
    {
        $this->migrator->delete('activity_log.other_retention_days');
        $this->migrator->delete('activity_log.deleted_retention_days');
        $this->migrator->delete('activity_log.updated_retention_days');
        $this->migrator->delete('activity_log.created_retention_days');
    }
};
