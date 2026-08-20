<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('accounting.auto_send_payment_advice', false);
    }

    public function down(): void
    {
        $this->migrator->delete('accounting.auto_send_payment_advice');
    }
};
