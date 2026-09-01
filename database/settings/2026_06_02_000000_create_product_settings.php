<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('product.variant_inheritance_enabled', true);
    }

    public function down(): void
    {
        $this->migrator->delete('product.variant_inheritance_enabled');
    }
};
