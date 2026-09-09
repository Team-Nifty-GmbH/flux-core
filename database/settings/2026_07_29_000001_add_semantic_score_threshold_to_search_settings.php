<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class() extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('search.semantic_score_threshold', 0);
    }

    public function down(): void
    {
        $this->migrator->delete('search.semantic_score_threshold');
    }
};
