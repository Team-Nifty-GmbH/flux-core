<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('flux.customer_comment_models', []);
    }

    public function down(): void
    {
        $this->migrator->delete('flux.customer_comment_models');
    }
};
