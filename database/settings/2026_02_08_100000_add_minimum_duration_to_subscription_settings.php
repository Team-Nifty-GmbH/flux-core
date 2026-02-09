<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('subscription.default_minimum_duration_value', 0);
        $this->migrator->add('subscription.default_minimum_duration_unit', 'months');
    }

    public function down(): void
    {
        $this->migrator->delete('subscription.default_minimum_duration_value');
        $this->migrator->delete('subscription.default_minimum_duration_unit');
    }
};
