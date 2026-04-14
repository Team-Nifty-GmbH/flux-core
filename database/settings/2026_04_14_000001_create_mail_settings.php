<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $default = config('mail.default');

        $this->migrator->add('mail.mailer', $default);
        $this->migrator->add('mail.host', config("mail.mailers.{$default}.host"));
        $this->migrator->add('mail.port', config("mail.mailers.{$default}.port"));
        $this->migrator->add('mail.username', config("mail.mailers.{$default}.username"));
        $this->migrator->add('mail.password', config("mail.mailers.{$default}.password"));
        $this->migrator->add('mail.encryption', config("mail.mailers.{$default}.encryption"));
        $this->migrator->add('mail.from_address', config('mail.from.address'));
        $this->migrator->add('mail.from_name', config('mail.from.name'));
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
