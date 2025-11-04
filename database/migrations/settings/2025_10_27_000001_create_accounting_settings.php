<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('accounting.auto_accept_secure_transaction_matches', false);
        $this->migrator->add('accounting.auto_send_reminders', false);
    }

    public function down(): void
    {
        $this->migrator->delete('accounting.auto_accept_secure_transaction_matches');
        $this->migrator->delete('accounting.auto_send_reminders');
    }
};
