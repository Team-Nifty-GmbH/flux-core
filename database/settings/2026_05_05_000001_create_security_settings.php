<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('security.force_two_factor', false);
        $this->migrator->add('security.magic_login_links_enabled', true);
    }

    public function down(): void
    {
        $this->migrator->delete('security.magic_login_links_enabled');
        $this->migrator->delete('security.force_two_factor');
    }
};
