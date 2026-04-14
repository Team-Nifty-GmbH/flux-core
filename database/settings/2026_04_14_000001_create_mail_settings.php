<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('mail.mailer', null);
        $this->migrator->add('mail.host', null);
        $this->migrator->add('mail.port', null);
        $this->migrator->add('mail.username', null);
        $this->migrator->add('mail.password', null);
        $this->migrator->add('mail.encryption', null);
        $this->migrator->add('mail.from_address', null);
        $this->migrator->add('mail.from_name', null);
    }

    public function down(): void
    {
        $this->migrator->delete('mail.mailer');
        $this->migrator->delete('mail.host');
        $this->migrator->delete('mail.port');
        $this->migrator->delete('mail.username');
        $this->migrator->delete('mail.password');
        $this->migrator->delete('mail.encryption');
        $this->migrator->delete('mail.from_address');
        $this->migrator->delete('mail.from_name');
    }
};
