<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('ticket.auto_reply_email_template_id', null);
    }

    public function down(): void
    {
        $this->migrator->delete('ticket.auto_reply_email_template_id');
    }
};
