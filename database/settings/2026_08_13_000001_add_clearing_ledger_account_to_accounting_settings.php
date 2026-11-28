<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('accounting.clearing_ledger_account_id', null);
    }

    public function down(): void
    {
        $this->migrator->delete('accounting.clearing_ledger_account_id');
    }
};
