<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('flux.license_key', config('flux.license_key'));
        $this->migrator->add('flux.install_done', config('flux.install_done', false));
        $this->migrator->add('flux.formal_salutation', config('flux.formal_salutation', false));
    }

    public function down(): void
    {
        $this->migrator->delete('flux.license_key');
        $this->migrator->delete('flux.install_done');
        $this->migrator->delete('flux.formal_salutation');
    }
};
