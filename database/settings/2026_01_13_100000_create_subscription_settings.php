<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('subscription.cancellation_text');
        $this->migrator->add('subscription.default_cancellation_notice_value', 0);
        $this->migrator->add('subscription.default_cancellation_notice_unit', 'days');
    }

    public function down(): void
    {
        $this->migrator->delete('subscription.cancellation_text');
        $this->migrator->delete('subscription.default_cancellation_notice_value');
        $this->migrator->delete('subscription.default_cancellation_notice_unit');
    }
};
